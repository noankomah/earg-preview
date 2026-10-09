<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/publication_helpers.php';
require_once __DIR__ . '/../includes/content_helpers.php';

require_admin();

$pdo = db();
$errors = [];

$data = [
    'title' => '',
    'publication_type' => 'Blog',
    'summary' => '',
    'full_content' => '',
    'author' => '',
    'publication_date' => '',
    'cover_image' => '',
    'document_link' => '',
    'is_published' => '1',
    'is_featured' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['title'] = trim((string) ($_POST['title'] ?? ''));
    $data['publication_type'] = trim((string) ($_POST['publication_type'] ?? ''));
    $data['summary'] = trim((string) ($_POST['summary'] ?? ''));
    $data['full_content'] = sanitize_rich_text((string) ($_POST['full_content'] ?? ''));
    $data['author'] = trim((string) ($_POST['author'] ?? ''));
    $data['publication_date'] = trim((string) ($_POST['publication_date'] ?? ''));
    $data['cover_image'] = trim((string) ($_POST['cover_image'] ?? ''));
    $data['document_link'] = trim((string) ($_POST['document_link'] ?? ''));
    $data['is_published'] = isset($_POST['is_published']) ? '1' : '0';
    $data['is_featured'] = isset($_POST['is_featured']) ? '1' : '0';

    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (!in_array($data['publication_type'], publication_types(), true)) {
        $errors[] = 'Please select a valid publication type.';
    }

    if ($data['summary'] === '') {
        $errors[] = 'Summary is required.';
    }

    if ($data['full_content'] === '') {
        $errors[] = 'Full content is required.';
    }

    if (
        $data['cover_image'] !== '' &&
        !filter_var($data['cover_image'], FILTER_VALIDATE_URL)
    ) {
        $errors[] = 'Cover image must be a valid URL.';
    }

    if (
        $data['document_link'] !== '' &&
        !filter_var($data['document_link'], FILTER_VALIDATE_URL)
    ) {
        $errors[] = 'Document link must be a valid URL.';
    }

    $publicationDate = clean_publication_date_or_null(
        $data['publication_date']
    );

    if (
        $data['publication_date'] !== '' &&
        $publicationDate === null
    ) {
        $errors[] = 'Publication date must be a valid date.';
    }

    if (!$errors) {
        $slug = unique_publication_slug($pdo, $data['title']);

        $statement = $pdo->prepare(
            "
            INSERT INTO publications (
                title,
                slug,
                publication_type,
                summary,
                full_content,
                author,
                publication_date,
                cover_image,
                document_link,
                is_published,
                is_featured
            )
            VALUES (
                :title,
                :slug,
                :publication_type,
                :summary,
                :full_content,
                :author,
                :publication_date,
                :cover_image,
                :document_link,
                :is_published,
                :is_featured
            )
            "
        );

        $statement->execute([
            ':title' => $data['title'],
            ':slug' => $slug,
            ':publication_type' => $data['publication_type'],
            ':summary' => $data['summary'],
            ':full_content' => $data['full_content'],
            ':author' => $data['author'] !== ''
                ? $data['author']
                : null,
            ':publication_date' => $publicationDate,
            ':cover_image' => $data['cover_image'] !== ''
                ? $data['cover_image']
                : null,
            ':document_link' => $data['document_link'] !== ''
                ? $data['document_link']
                : null,
            ':is_published' => (int) $data['is_published'],
            ':is_featured' => (int) $data['is_featured'],
        ]);

        header('Location: index.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Publication | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link
        href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
        rel="stylesheet"
    >
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Add Publication';
    $adminLinks = array(
        array('Back to publications', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">New publication</p>
            <h1>Add publication</h1>

            <?php if ($errors): ?>
                <div class="admin-alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="admin-form admin-wide-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <label>
                    Title
                    <input
                        type="text"
                        name="title"
                        value="<?= e($data['title']) ?>"
                        required
                    >
                </label>

                <label>
                    Publication type
                    <select name="publication_type" required>
                        <?php foreach (publication_types() as $type): ?>
                            <option
                                value="<?= e($type) ?>"
                                <?= $data['publication_type'] === $type
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                <?= e($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Short summary
                    <textarea
                        name="summary"
                        rows="4"
                        required
                    ><?= e($data['summary']) ?></textarea>
                </label>

                <label for="full_content_editor">
                    Full content
                </label>

                <input
                    type="hidden"
                    name="full_content"
                    id="full_content"
                    value="<?= e($data['full_content']) ?>"
                >

                <div
                    id="full_content_editor"
                    class="admin-rich-text-editor"
                    data-rich-text-editor
                    data-target="full_content"
                    data-placeholder="Enter the full publication content..."
                ></div>

                <div class="admin-two-cols">
                    <label>
                        Author
                        <input
                            type="text"
                            name="author"
                            value="<?= e($data['author']) ?>"
                        >
                    </label>

                    <label>
                        Publication date
                        <input
                            type="date"
                            name="publication_date"
                            value="<?= e($data['publication_date']) ?>"
                        >
                    </label>
                </div>

                <label>
                    Cover image URL
                    <input
                        type="url"
                        name="cover_image"
                        value="<?= e($data['cover_image']) ?>"
                        placeholder="https://example.org/image.jpg"
                    >
                </label>

                <label>
                    Document link
                    <input
                        type="url"
                        name="document_link"
                        value="<?= e($data['document_link']) ?>"
                        placeholder="https://example.org/document.pdf"
                    >
                </label>

                <div class="admin-checks">
                    <label>
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            <?= $data['is_published'] === '1'
                                ? 'checked'
                                : ''
                            ?>
                        >
                        Publish on website
                    </label>

                    <label>
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            <?= $data['is_featured'] === '1'
                                ? 'checked'
                                : ''
                            ?>
                        >
                        Mark as featured
                    </label>
                </div>

                <button type="submit">Save publication</button>
            </form>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="../assets/rich-text.js"></script>
</body>
</html>
