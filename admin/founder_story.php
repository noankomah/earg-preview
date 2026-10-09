<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/content_helpers.php';

require_admin();

$pdo = db();

// Uploads are stored under the public assets folder so the website can
// serve them directly, and referenced by a site-relative path in the DB.
const FOUNDER_UPLOAD_DIR = __DIR__ . '/../assets/uploads/founder/';
const FOUNDER_UPLOAD_WEB_PATH = 'assets/uploads/founder/';

/**
 * Validate an uploaded founder photo and move it into the uploads folder.
 *
 * Returns ['success' => true, 'path' => ...] on success, or
 * ['success' => false, 'error' => message] on failure.
 */
function handle_photo_upload(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['success' => false, 'error' => 'The photo is too large. Maximum size is 3 MB.'];
        }

        return ['success' => false, 'error' => 'The photo could not be uploaded. Please try again.'];
    }

    if ((int) $file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'error' => 'The photo is too large. Maximum size is 3 MB.'];
    }

    $info = getimagesize($file['tmp_name']);

    if ($info === false) {
        return ['success' => false, 'error' => 'The uploaded file is not a valid image.'];
    }

    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];

    if (!in_array($info[2], $allowedTypes, true)) {
        return ['success' => false, 'error' => 'Only PNG, JPG, WEBP and GIF images are allowed.'];
    }

    $extension = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
    ][$info[2]];

    $filename = 'founder-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

    if (!is_dir(FOUNDER_UPLOAD_DIR) && !mkdir(FOUNDER_UPLOAD_DIR, 0755, true)) {
        return ['success' => false, 'error' => 'The uploads folder could not be created.'];
    }

    if (!move_uploaded_file($file['tmp_name'], FOUNDER_UPLOAD_DIR . $filename)) {
        return ['success' => false, 'error' => 'The photo could not be saved. Please try again.'];
    }

    return ['success' => true, 'path' => FOUNDER_UPLOAD_WEB_PATH . $filename];
}

/**
 * Remove an uploaded founder photo file (safe when called with an old path).
 */
function delete_uploaded_photo(string $path): void
{
    // Only ever delete files that live inside our own uploads folder.
    $base = realpath(FOUNDER_UPLOAD_DIR);

    if ($base === false) {
        return;
    }

    $fullPath = realpath(__DIR__ . '/../' . $path);

    if ($fullPath !== false && str_starts_with($fullPath, $base . DIRECTORY_SEPARATOR)) {
        @unlink($fullPath);
    }
}

// The founder story is a single record. Load the current one (row id 1),
// or start with empty values if it has not been created yet.
$row = $pdo
    ->query("SELECT * FROM founder_story WHERE id = 1 LIMIT 1")
    ->fetch();

$errors = [];

// Sections hold the story as named parts (e.g. Personal Information,
// Education, Work Experience). Each section is heading + rich text body.
// This array is what the editor works with; it is initialised from the
// saved record, or from the submitted form when validation fails.
$sectionsForForm = [];

function parse_story_sections(?string $raw): array
{
    $sections = [];

    if ($raw === null || trim($raw) === '') {
        return $sections;
    }

    $decoded = json_decode($raw, true);

    if (is_array($decoded)) {
        foreach ($decoded as $section) {
            $sections[] = [
                'heading' => (string) ($section['heading'] ?? ''),
                'body' => (string) ($section['body'] ?? ''),
            ];
        }

        return $sections;
    }

    // Fallback for older content saved as a single block of rich text
    // rather than the JSON sections format.
    return [
        [
            'heading' => '',
            'body' => $raw,
        ],
    ];
}

$sectionsForForm = parse_story_sections($row ? (string) $row['sections'] : null);

$data = [
    'founder_name' => (string) ($row['founder_name'] ?? ''),
    'role_title' => (string) ($row['role_title'] ?? ''),
    'photo_url' => (string) ($row['photo_url'] ?? ''),
    'is_published' => $row ? (string) $row['is_published'] : '1',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['founder_name'] = trim((string) ($_POST['founder_name'] ?? ''));
    $data['role_title'] = trim((string) ($_POST['role_title'] ?? ''));
    $data['is_published'] = isset($_POST['is_published']) ? '1' : '0';
    $removePhoto = isset($_POST['remove_photo']);

    // Sections arrive as a JSON string from the hidden form field.
    $sectionsFromPost = json_decode((string) ($_POST['sections'] ?? '[]'), true);

    if (!is_array($sectionsFromPost)) {
        $sectionsFromPost = [];
    }

    $savedSections = [];
    $sectionsForForm = [];

    foreach ($sectionsFromPost as $section) {
        $heading = trim((string) ($section['heading'] ?? ''));
        $body = sanitize_rich_text((string) ($section['body'] ?? ''));

        // Keep everything the user typed so edits survive a failed save.
        $sectionsForForm[] = [
            'heading' => $heading,
            'body' => $body,
        ];

        // Only persist sections that have any content.
        if ($heading !== '' || $body !== '') {
            $savedSections[] = [
                'heading' => $heading,
                'body' => $body,
            ];
        }
    }

    if ($data['founder_name'] === '') {
        $errors[] = 'Founder name is required.';
    }

    if (!$savedSections) {
        $errors[] = 'Add at least one story section.';
    }

    if (!$errors) {
        // Handle an uploaded founder photo (replaces any existing photo).
        // Uploads are only processed once the other fields are valid.
        $uploadedPhoto = null;

        if (!empty($_FILES['photo']['name'])) {
            $photoResult = handle_photo_upload($_FILES['photo']);

            if ($photoResult['success']) {
                $uploadedPhoto = $photoResult['path'];
            } else {
                $errors[] = $photoResult['error'];
            }
        }

        // Keep the existing photo unless a new one was uploaded or it was removed.
        if ($uploadedPhoto !== null) {
            $data['photo_url'] = $uploadedPhoto;
        } elseif ($removePhoto) {
            $data['photo_url'] = '';
        } else {
            $data['photo_url'] = (string) ($row['photo_url'] ?? '');
        }

        // Delete the old photo file when it is replaced or removed.
        if (!$errors && $row && $data['photo_url'] !== $row['photo_url'] && $row['photo_url']) {
            delete_uploaded_photo((string) $row['photo_url']);
        }
        if ($row) {
            // Update the existing record.
            $statement = $pdo->prepare("
                UPDATE founder_story
                SET
                    founder_name = :founder_name,
                    role_title = :role_title,
                    photo_url = :photo_url,
                    sections = :sections,
                    is_published = :is_published
                WHERE id = 1
            ");
        } else {
            // Create the record for the first time.
            $statement = $pdo->prepare("
                INSERT INTO founder_story (
                    id,
                    founder_name,
                    role_title,
                    photo_url,
                    sections,
                    is_published
                )
                VALUES (
                    1,
                    :founder_name,
                    :role_title,
                    :photo_url,
                    :sections,
                    :is_published
                )
            ");
        }

        $statement->execute([
            ':founder_name' => $data['founder_name'],
            ':role_title' => $data['role_title'] !== '' ? $data['role_title'] : null,
            ':photo_url' => $data['photo_url'] !== '' ? $data['photo_url'] : null,
            ':sections' => json_encode($savedSections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':is_published' => (int) $data['is_published'],
        ]);

        header('Location: founder_story.php?saved=1');
        exit;
    }
}

// Encode the current sections so the editor can recreate each Quill editor.
// JSON_HEX_TAG escapes < and > so no "</script>" can break out of the
// JSON script block on the page.
$sectionsDataJson = json_encode(
    $sectionsForForm,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Founder Story | EA Research Group</title>
    <link rel="stylesheet" href="assets/admin.css?v=5">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Founder Story';
    $adminLinks = array(
        array('Dashboard', 'dashboard.php'),
        array('Log out', 'logout.php'),
    );
    ?>
    <?php include __DIR__ . '/includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">Founder story</p>
            <h1>Founder's story</h1>
            <p class="admin-muted">
                This content is shown on the public <em>Founder's Story</em> page.
            </p>

            <?php if (isset($_GET['saved'])): ?>
                <div class="admin-success">
                    Founder story saved successfully.
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="admin-alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="admin-form admin-wide-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label>
                    Founder name
                    <input type="text" name="founder_name" value="<?= e($data['founder_name']) ?>" required>
                </label>

                <div class="admin-two-cols">
                    <label>
                        Role / title
                        <input type="text" name="role_title" value="<?= e($data['role_title']) ?>" placeholder="e.g. Founder & Executive Director">
                    </label>

                    <label for="photo">
                        Founder photo (upload)
                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept="image/png,image/jpeg,image/webp,image/gif"
                            data-photo-input
                        >
                        <span class="admin-field-help">
                            PNG, JPG, WEBP or GIF. Maximum 3 MB.
                        </span>
                    </label>
                </div>

                <div
                    class="admin-photo-preview admin-photo-preview-new"
                    data-photo-preview
                    hidden
                >
                    <img data-photo-preview-img alt="New photo preview">

                    <div class="admin-photo-preview-meta">
                        <strong data-photo-preview-name></strong>
                        <span data-photo-preview-size></span>
                    </div>
                </div>

                <?php if ($data['photo_url'] !== ''): ?>
                    <div class="admin-photo-preview" data-current-photo>
                        <img src="../<?= e($data['photo_url']) ?>" alt="Current founder photo">

                        <label>
                            <input type="checkbox" name="remove_photo" value="1">
                            Remove current photo
                        </label>
                    </div>
                <?php endif; ?>

                <label>Story sections</label>

                <p class="admin-muted admin-sections-hint">
                    Add the story in parts, such as Personal Information,
                    Education and Work Experience.
                </p>

                <script type="application/json" data-sections-data><?= $sectionsDataJson ?></script>

                <input type="hidden" name="sections" data-sections-input>

                <div class="admin-sections" data-sections></div>

                <button type="button" class="admin-add-section" data-add-section>
                    + Add section
                </button>

                <div class="admin-checks">
                    <label>
                        <input type="checkbox" name="is_published" value="1" <?= $data['is_published'] === '1' ? 'checked' : '' ?>>
                        Publish on website
                    </label>
                </div>

                <button type="submit" class="admin-submit-button" data-save-button>
                    <span class="admin-spinner" data-save-spinner hidden></span>
                    <span data-save-label>Save founder story</span>
                </button>
            </form>

            <div class="admin-save-overlay" data-save-overlay hidden>
                <div class="admin-save-overlay-card">
                    <span class="admin-spinner admin-spinner-large"></span>
                    <p>Saving founder details...</p>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const photoInput = document.querySelector("[data-photo-input]");
            const previewBlock = document.querySelector("[data-photo-preview]");
            const previewImg = document.querySelector("[data-photo-preview-img]");
            const previewName = document.querySelector("[data-photo-preview-name]");
            const previewSize = document.querySelector("[data-photo-preview-size]");
            const currentPhotoBlock = document.querySelector("[data-current-photo]");

            let previewUrl = null;

            function formatSize(bytes) {
                if (bytes < 1024) return bytes + " B";
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
                return (bytes / (1024 * 1024)).toFixed(1) + " MB";
            }

            if (photoInput && previewBlock) {
                photoInput.addEventListener("change", function () {
                    const file = photoInput.files && photoInput.files[0];

                    if (!file) {
                        // Nothing selected: hide the new preview, restore the
                        // current photo preview if there is one.
                        previewBlock.hidden = true;
                        if (currentPhotoBlock) currentPhotoBlock.hidden = false;
                        return;
                    }

                    if (previewUrl) URL.revokeObjectURL(previewUrl);
                    previewUrl = URL.createObjectURL(file);
                    previewImg.src = previewUrl;
                    previewImg.alt = file.name;
                    previewName.textContent = file.name;
                    previewSize.textContent = formatSize(file.size);
                    previewBlock.hidden = false;

                    // A new photo will replace the current one on save.
                    if (currentPhotoBlock) currentPhotoBlock.hidden = true;
                });
            }

            // ----- Story sections -----
            const sectionsDataEl = document.querySelector("[data-sections-data]");
            const sectionsContainer = document.querySelector("[data-sections]");
            const sectionsInput = document.querySelector("[data-sections-input]");
            const addSectionButton = document.querySelector("[data-add-section]");

            let sections = [];
            let quillInstances = [];

            // Load the saved sections (or start with one empty section).
            try {
                const parsed = JSON.parse(sectionsDataEl.textContent || "[]");
                sections = Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                sections = [];
            }

            if (sections.length === 0) {
                sections.push({ heading: "", body: "" });
            }

            function destroyQuills() {
                quillInstances = [];
            }

            function renderSections() {
                destroyQuills();
                sectionsContainer.innerHTML = "";

                sections.forEach(function (section, index) {
                    const row = document.createElement("div");
                    row.className = "admin-section-row";

                    const head = document.createElement("div");
                    head.className = "admin-section-head";

                    const titleInput = document.createElement("input");
                    titleInput.type = "text";
                    titleInput.className = "admin-section-heading-input";
                    titleInput.placeholder = "Section title (e.g. Personal Information)";
                    titleInput.value = section.heading;
                    titleInput.addEventListener("input", function () {
                        sections[index].heading = titleInput.value;
                    });

                    const removeButton = document.createElement("button");
                    removeButton.type = "button";
                    removeButton.className = "admin-section-remove";
                    removeButton.textContent = "Remove section";
                    removeButton.addEventListener("click", function () {
                        sections.splice(index, 1);
                        renderSections();
                    });

                    head.appendChild(titleInput);
                    head.appendChild(removeButton);

                    const editorEl = document.createElement("div");
                    editorEl.className = "admin-section-editor";

                    row.appendChild(head);
                    row.appendChild(editorEl);
                    sectionsContainer.appendChild(row);

                    if (typeof Quill === "undefined") {
                        sectionsContainer.removeChild(row);
                        return;
                    }

                    let quill = null;
                    try {
                        quill = new Quill(editorEl, {
                            theme: "snow",
                            placeholder: "Write this section...",
                            modules: {
                                toolbar: [
                                    [{ header: [2, 3, false] }],
                                    ["bold", "italic"],
                                    [{ list: "ordered" }, { list: "bullet" }],
                                    ["link"],
                                    ["clean"]
                                ]
                            },
                            formats: ["header", "bold", "italic", "list", "link"]
                        });
                    } catch (error) {
                        quill = null;
                    }

                    if (quill !== null) {
                        quillInstances.push(quill);

                        if (section.body.trim() !== "") {
                            quill.clipboard.dangerouslyPasteHTML(section.body);
                            sections[index].body = quill.root.innerHTML;
                        }

                        quill.on("text-change", function () {
                            sections[index].body = quill.root.innerHTML;
                        });
                    }
                });
            }

            if (sectionsContainer && addSectionButton) {
                addSectionButton.addEventListener("click", function () {
                    sections.push({ heading: "", body: "" });
                    renderSections();
                });

                renderSections();
            }

            // ----- Save button / spinner -----
            const saveButton = document.querySelector("[data-save-button]");
            const saveSpinner = document.querySelector("[data-save-spinner]");
            const saveLabel = document.querySelector("[data-save-label]");
            const saveOverlay = document.querySelector("[data-save-overlay]");

            if (saveButton && saveSpinner && saveLabel) {
                const form = saveButton.closest("form");

                form.addEventListener("submit", function (event) {
                    const file = photoInput && photoInput.files && photoInput.files[0];

                    if (file && !file.type.startsWith("image/")) {
                        event.preventDefault();
                        return;
                    }

                    // Serialize the sections into the hidden field for saving.
                    if (sectionsInput) {
                        sectionsInput.value = JSON.stringify(sections);
                    }

                    // Show the full-screen spinner; it stays visible until the
                    // browser navigates to the reloaded page after saving.
                    saveSpinner.hidden = false;
                    saveLabel.textContent = "Saving...";
                    saveButton.disabled = true;
                    if (saveOverlay) saveOverlay.hidden = false;
                });
            }
        });
    </script>
</body>
</html>