<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/hero_helpers.php';

require_admin();

$pdo = db();
$errors = [];
$slide = null;
$loadError = null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(404);
    exit('Hero slide not found.');
}

try {
    $statement = $pdo->prepare(
        "
        SELECT *
        FROM hero_slides
        WHERE id = :id
        LIMIT 1
        "
    );

    $statement->execute([
        ':id' => $id,
    ]);

    $slide = $statement->fetch();
} catch (Throwable $exception) {
    error_log('Admin hero load error: ' . $exception->getMessage());

    $loadError = hero_error_message($exception);
}

if (!$slide && !$loadError) {
    http_response_code(404);
    exit('Hero slide not found.');
}

$data = [
    'image_url' => (string) ($slide['image_url'] ?? ''),
    'position' => (string) ($slide['position'] ?? 'center center'),
    'display_order' => (string) ($slide['display_order'] ?? '0'),
    'is_visible' => (string) (int) ($slide['is_visible'] ?? 1),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $slide) {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['position'] = trim((string) ($_POST['position'] ?? ''));
    $data['display_order'] = trim((string) ($_POST['display_order'] ?? ''));
    $data['is_visible'] = isset($_POST['is_visible']) ? '1' : '0';

    if ($data['position'] === '') {
        $data['position'] = 'center center';
    }

    if (filter_var($data['display_order'], FILTER_VALIDATE_INT) === false) {
        $errors[] = 'Display order must be a whole number.';
    }

    $previousImage = (string) ($slide['image_url'] ?? '');

    if (!$errors) {
        // A newly uploaded image replaces the current one; leaving the file
        // field empty keeps the existing photo.
        $uploadedImage = null;

        if (!empty($_FILES['image']['name'])) {
            $imageResult = handle_hero_image_upload($_FILES['image']);

            if ($imageResult['success']) {
                $uploadedImage = $imageResult['path'];
            } else {
                $errors[] = $imageResult['error'];
            }
        }

        $data['image_url'] = $uploadedImage ?? $previousImage;
    }

    if (!$errors) {
        try {
            $statement = $pdo->prepare(
                "
                UPDATE hero_slides
                SET
                    image_url = :image_url,
                    position = :position,
                    display_order = :display_order,
                    is_visible = :is_visible
                WHERE id = :id
                "
            );

            $statement->execute([
                ':image_url' => $data['image_url'],
                ':position' => $data['position'],
                ':display_order' => (int) $data['display_order'],
                ':is_visible' => (int) $data['is_visible'],
                ':id' => (int) $slide['id'],
            ]);
        } catch (Throwable $exception) {
            error_log('Admin hero update error: ' . $exception->getMessage());

            $errors[] = hero_error_message($exception);

            // The row is unchanged, so an image added here would be orphaned.
            if ($uploadedImage !== null) {
                delete_hero_image($uploadedImage);
                $data['image_url'] = $previousImage;
            }
        }

        if (!$errors) {
            // The record now points at the new image: drop the old file.
            if ($data['image_url'] !== $previousImage && $previousImage !== '') {
                delete_hero_image($previousImage);
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
    <title>Edit Hero Slide | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Edit Hero Slide';
    $adminLinks = array(
        array('Back to slider', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <?php if ($loadError): ?>
            <section class="admin-form-card">
                <p class="admin-eyebrow">Hero slide</p>
                <h1>Cannot edit this slide</h1>

                <div class="admin-alert">
                    <p><?= e($loadError) ?></p>
                </div>

                <a class="admin-cancel-link" href="index.php">Back to slider</a>
            </section>
        <?php else: ?>
            <section class="admin-form-card">
                <p class="admin-eyebrow">Edit slide</p>
                <h1>Slide #<?= (int) $slide['id'] ?></h1>

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
                        Replace image (optional)
                        <input
                            type="file"
                            name="image"
                            accept="image/png,image/jpeg,image/webp,image/gif"
                        >
                        <span class="admin-field-help">
                            PNG, JPG, WEBP or GIF. Maximum 3 MB. Leave empty to keep
                            the current photo.
                        </span>
                    </label>

                    <?php if ($data['image_url'] !== ''): ?>
                        <div class="admin-photo-preview" data-current-photo>
                            <img src="../../<?= e($data['image_url']) ?>" alt="Current slide image">
                        </div>
                    <?php endif; ?>

                    <div class="admin-two-cols">
                        <label>
                            Position
                            <input
                                type="text"
                                name="position"
                                value="<?= e($data['position']) ?>"
                                maxlength="60"
                                placeholder="center center"
                            >
                            <span class="admin-field-help">
                                Which part of the photo stays in view.
                            </span>
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
                                Lower numbers rotate first on the home page.
                            </span>
                        </label>
                    </div>

                    <div class="admin-checks">
                        <label>
                            <input
                                type="checkbox"
                                name="is_visible"
                                value="1"
                                <?= $data['is_visible'] === '1' ? 'checked' : '' ?>
                            >
                            Show on the home page
                        </label>
                    </div>

                    <button type="submit">Save changes</button>

                    <a class="admin-cancel-link" href="index.php">Cancel</a>
                </form>
            </section>
        <?php endif; ?>
    </main>

</body>
</html>
