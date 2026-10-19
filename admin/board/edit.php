<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/board_helpers.php';

require_admin();

$pdo = db();
$errors = [];
$member = null;
$loadError = null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(404);
    exit('Board member not found.');
}

try {
    $statement = $pdo->prepare(
        "
        SELECT *
        FROM board_members
        WHERE id = :id
        LIMIT 1
        "
    );

    $statement->execute([
        ':id' => $id,
    ]);

    $member = $statement->fetch();
} catch (Throwable $exception) {
    error_log('Admin board load error: ' . $exception->getMessage());

    $loadError = board_error_message($exception);
}

if (!$member && !$loadError) {
    http_response_code(404);
    exit('Board member not found.');
}

$data = [
    'name' => (string) ($member['name'] ?? ''),
    'role_title' => (string) ($member['role_title'] ?? ''),
    'photo_url' => (string) ($member['photo_url'] ?? ''),
    'display_order' => (string) ($member['display_order'] ?? '0'),
    'is_visible' => (string) (int) ($member['is_visible'] ?? 1),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $member) {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['name'] = trim((string) ($_POST['name'] ?? ''));
    $data['role_title'] = trim((string) ($_POST['role_title'] ?? ''));
    $data['display_order'] = trim((string) ($_POST['display_order'] ?? ''));
    $data['is_visible'] = isset($_POST['is_visible']) ? '1' : '0';

    $removePhoto = isset($_POST['remove_photo']);

    if ($data['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if (filter_var($data['display_order'], FILTER_VALIDATE_INT) === false) {
        $errors[] = 'Display order must be a whole number.';
    }

    $previousPhoto = (string) ($member['photo_url'] ?? '');

    if (!$errors) {
        // A new photo replaces the current one; the check box removes it.
        $uploadedPhoto = null;

        if (!empty($_FILES['photo']['name'])) {
            $photoResult = handle_board_image_upload($_FILES['photo']);

            if ($photoResult['success']) {
                $uploadedPhoto = $photoResult['path'];
            } else {
                $errors[] = $photoResult['error'];
            }
        }

        if ($uploadedPhoto !== null) {
            $data['photo_url'] = $uploadedPhoto;
        } elseif ($removePhoto) {
            $data['photo_url'] = '';
        } else {
            $data['photo_url'] = $previousPhoto;
        }
    }

    if (!$errors) {
        try {
            $statement = $pdo->prepare(
                "
                UPDATE board_members
                SET
                    name = :name,
                    role_title = :role_title,
                    photo_url = :photo_url,
                    display_order = :display_order,
                    is_visible = :is_visible
                WHERE id = :id
                "
            );

            $statement->execute([
                ':name' => $data['name'],
                ':role_title' => $data['role_title'] !== '' ? $data['role_title'] : null,
                ':photo_url' => $data['photo_url'] !== '' ? $data['photo_url'] : null,
                ':display_order' => (int) $data['display_order'],
                ':is_visible' => (int) $data['is_visible'],
                ':id' => (int) $member['id'],
            ]);
        } catch (Throwable $exception) {
            error_log('Admin board update error: ' . $exception->getMessage());

            $errors[] = board_error_message($exception);

            // The row is unchanged, so a photo added here would be orphaned.
            if ($uploadedPhoto !== null) {
                delete_board_image($uploadedPhoto);
                $data['photo_url'] = $previousPhoto;
            }
        }

        if (!$errors) {
            // The record now points at the new photo: drop the old file.
            if ($data['photo_url'] !== $previousPhoto && $previousPhoto !== '') {
                delete_board_image($previousPhoto);
            }

            header('Location: index.php?updated=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Board Member | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Edit Board Member';
    $adminLinks = array(
        array('Back to board', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <?php if ($loadError): ?>
            <section class="admin-form-card">
                <p class="admin-eyebrow">Board member</p>
                <h1>Cannot edit this member</h1>

                <div class="admin-alert">
                    <p><?= e($loadError) ?></p>
                </div>

                <a class="admin-cancel-link" href="index.php">Back to board</a>
            </section>
        <?php else: ?>
            <section class="admin-form-card">
                <p class="admin-eyebrow">Edit board member</p>
                <h1><?= e($data['name']) ?></h1>

                <?php if ($errors): ?>
                    <div class="admin-alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?= e($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="admin-form admin-wide-form" enctype="multipart/form-data">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <label>
                        Full name
                        <input
                            type="text"
                            name="name"
                            value="<?= e($data['name']) ?>"
                            maxlength="120"
                            required
                        >
                    </label>

                    <div class="admin-two-cols">
                        <label>
                            Role / title
                            <input
                                type="text"
                                name="role_title"
                                value="<?= e($data['role_title']) ?>"
                                maxlength="120"
                                placeholder="e.g. Board Chair"
                            >
                        </label>

                        <label>
                            Display order
                            <input
                                type="number"
                                name="display_order"
                                value="<?= e($data['display_order']) ?>"
                                step="1"
                            >
                            <span class="admin-field-help">
                                Lower numbers appear first on the page.
                            </span>
                        </label>
                    </div>

                    <label>
                        Photo
                        <input
                            type="file"
                            name="photo"
                            accept="image/png,image/jpeg,image/webp,image/gif"
                            data-photo-input
                        >
                        <span class="admin-field-help">
                            PNG, JPG, WEBP or GIF. Maximum 3 MB. Choosing a new
                            photo replaces the current one.
                        </span>
                    </label>

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
                            <img src="../../<?= e($data['photo_url']) ?>" alt="Current photo of <?= e($data['name']) ?>">

                            <label>
                                <input type="checkbox" name="remove_photo" value="1">
                                Remove current photo
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="admin-checks">
                        <label>
                            <input
                                type="checkbox"
                                name="is_visible"
                                value="1"
                                <?= $data['is_visible'] === '1' ? 'checked' : '' ?>
                            >
                            Show on the Governing Board page
                        </label>
                    </div>

                    <button type="submit">Save changes</button>

                    <a class="admin-cancel-link" href="index.php">Cancel</a>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <script src="../assets/photo-preview.js?v=1"></script>

</body>
</html>
