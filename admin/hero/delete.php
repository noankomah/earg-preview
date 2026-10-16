<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/hero_helpers.php';

require_admin();

$pdo = db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(404);
    exit('Hero slide not found.');
}

$slide = null;
$loadError = null;

try {
    $statement = $pdo->prepare(
        "
        SELECT id, image_url, position, display_order
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
    error_log('Admin hero delete load error: ' . $exception->getMessage());

    $loadError = hero_error_message($exception);
}

if (!$slide && !$loadError) {
    http_response_code(404);
    exit('Hero slide not found.');
}

$errors = [];

// The delete button is a POST with a CSRF token, so a link on another site can
// never remove a slide. After the row is gone we go back to the list with a
// success message (Post/Redirect/Get).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $slide) {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if (!$errors) {
        try {
            $delete = $pdo->prepare(
                "
                DELETE FROM hero_slides
                WHERE id = :id
                "
            );

            $delete->execute([
                ':id' => $id,
            ]);
        } catch (Throwable $exception) {
            error_log('Admin hero delete error: ' . $exception->getMessage());

            $errors[] = hero_error_message($exception);
        }

        if (!$errors) {
            // Only now that the row is gone is the image file safe to remove.
            // Seeded slides live outside the uploads folder and are left alone.
            delete_hero_image((string) ($slide['image_url'] ?? ''));

            header('Location: index.php?deleted=1');
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
    <title>Delete Hero Slide | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Delete Hero Slide';
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
                <h1>Cannot delete this slide</h1>

                <div class="admin-alert">
                    <p><?= e($loadError) ?></p>
                </div>

                <a class="admin-cancel-link" href="index.php">Back to slider</a>
            </section>
        <?php else: ?>
            <section class="admin-form-card">
                <p class="admin-eyebrow">Delete slide</p>
                <h1>Remove this slide from the home page?</h1>

                <div class="admin-photo-preview">
                    <img src="../../<?= e((string) $slide['image_url']) ?>" alt="Slide image">
                </div>

                <?php if ($errors): ?>
                    <div class="admin-alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?= e($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="admin-alert">
                    <p>
                        This action cannot be undone. Uploaded photos are deleted;
                        the photos that shipped with the site are kept on disk.
                    </p>
                </div>

                <form method="post" class="admin-form">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <button type="submit" class="admin-danger-button">
                        Delete slide
                    </button>
                </form>
            </section>
        <?php endif; ?>
    </main>

</body>
</html>
