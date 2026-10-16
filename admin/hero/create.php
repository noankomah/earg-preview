<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/hero_helpers.php';

require_admin();

$pdo = db();
$errors = [];

$data = [
    'position' => 'center center',
    'display_order' => '',
    'is_visible' => '1',
];

// A new slide is normally placed after everyone else. The table may not exist
// yet, so fall back to "1" instead of failing before the form shows.
try {
    $data['display_order'] = (string) hero_next_display_order($pdo);
} catch (Throwable $exception) {
    error_log('Admin hero order default error: ' . $exception->getMessage());

    $data['display_order'] = '1';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['position'] = trim((string) ($_POST['position'] ?? ''));
    $data['display_order'] = trim((string) ($_POST['display_order'] ?? ''));
    $data['is_visible'] = isset($_POST['is_visible']) ? '1' : '0';

    if ($data['position'] === '') {
        $data['position'] = 'center center';
    }

    if ($data['display_order'] === '') {
        // Left blank: add the slide after everyone else.
        $data['display_order'] = (string) hero_next_display_order($pdo);
    }

    if (filter_var($data['display_order'], FILTER_VALIDATE_INT) === false) {
        $errors[] = 'Display order must be a whole number.';
    }

    if (empty($_FILES['image']['name'])) {
        $errors[] = 'Please choose an image for the slide.';
    }

    if (!$errors) {
        // The image is only uploaded once the other fields are valid, so a
        // failed save never leaves an orphan file behind.
        $imagePath = '';

        $imageResult = handle_hero_image_upload($_FILES['image']);

        if ($imageResult['success']) {
            $imagePath = $imageResult['path'];
        } else {
            $errors[] = $imageResult['error'];
        }

        if (!$errors) {
            try {
                $statement = $pdo->prepare(
                    "
                    INSERT INTO hero_slides (
                        image_url,
                        position,
                        display_order,
                        is_visible
                    )
                    VALUES (
                        :image_url,
                        :position,
                        :display_order,
                        :is_visible
                    )
                    "
                );

                $statement->execute([
                    ':image_url' => $imagePath,
                    ':position' => $data['position'],
                    ':display_order' => (int) $data['display_order'],
                    ':is_visible' => (int) $data['is_visible'],
                ]);

                header('Location: index.php?created=1');
                exit;
            } catch (Throwable $exception) {
                error_log('Admin hero create error: ' . $exception->getMessage());

                $errors[] = hero_error_message($exception);

                // Remove the image we just stored, it belongs to no record.
                if ($imagePath !== '') {
                    delete_hero_image($imagePath);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hero Slide | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Add Hero Slide';
    $adminLinks = array(
        array('Back to slider', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">New slide</p>
            <h1>Add hero slide</h1>

            <p class="admin-muted">
                The photo joins the rotation on the home page once saved.
            </p>

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
                    Image
                    <input
                        type="file"
                        name="image"
                        accept="image/png,image/jpeg,image/webp,image/gif"
                        required
                    >
                    <span class="admin-field-help">
                        A wide photo works best (landscape). PNG, JPG, WEBP or GIF.
                        Maximum 3 MB.
                    </span>
                </label>

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
                            Which part of the photo stays in view, e.g.
                            <code>center 45%</code> or <code>center top</code>.
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

                <button type="submit">Save slide</button>

                <a class="admin-cancel-link" href="index.php">Cancel</a>
            </form>
        </section>
    </main>

</body>
</html>
