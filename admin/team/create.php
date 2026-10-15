<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/team_helpers.php';

require_admin();

$pdo = db();
$errors = [];

$data = [
    'name' => '',
    'role_title' => '',
    'display_order' => '',
    'is_visible' => '1',
];

// A new member is normally placed after everyone else. The table may not
// exist yet, so fall back to "1" instead of failing before the form shows.
try {
    $data['display_order'] = (string) team_next_display_order($pdo);
} catch (Throwable $exception) {
    error_log('Admin team order default error: ' . $exception->getMessage());

    $data['display_order'] = '1';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['name'] = trim((string) ($_POST['name'] ?? ''));
    $data['role_title'] = trim((string) ($_POST['role_title'] ?? ''));
    $data['display_order'] = trim((string) ($_POST['display_order'] ?? ''));
    $data['is_visible'] = isset($_POST['is_visible']) ? '1' : '0';

    if ($data['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($data['display_order'] === '') {
        // Left blank: add the card after everyone else.
        $data['display_order'] = (string) team_next_display_order($pdo);
    }

    if (filter_var($data['display_order'], FILTER_VALIDATE_INT) === false) {
        $errors[] = 'Display order must be a whole number.';
    }

    if (!$errors) {
        // The photo is only uploaded once the other fields are valid, so a
        // failed save never leaves an orphan file behind.
        $photoPath = '';

        if (!empty($_FILES['photo']['name'])) {
            $photoResult = handle_team_image_upload($_FILES['photo']);

            if ($photoResult['success']) {
                $photoPath = $photoResult['path'];
            } else {
                $errors[] = $photoResult['error'];
            }
        }

        if (!$errors) {
            try {
                $statement = $pdo->prepare(
                    "
                    INSERT INTO team_members (
                        name,
                        role_title,
                        photo_url,
                        display_order,
                        is_visible
                    )
                    VALUES (
                        :name,
                        :role_title,
                        :photo_url,
                        :display_order,
                        :is_visible
                    )
                    "
                );

                $statement->execute([
                    ':name' => $data['name'],
                    ':role_title' => $data['role_title'] !== '' ? $data['role_title'] : null,
                    ':photo_url' => $photoPath !== '' ? $photoPath : null,
                    ':display_order' => (int) $data['display_order'],
                    ':is_visible' => (int) $data['is_visible'],
                ]);

                header('Location: index.php?created=1');
                exit;
            } catch (Throwable $exception) {
                error_log('Admin team create error: ' . $exception->getMessage());

                $errors[] = team_error_message($exception);

                // Remove the photo we just stored, it belongs to no record.
                if ($photoPath !== '') {
                    delete_team_image($photoPath);
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
    <title>Add Team Member | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Add Team Member';
    $adminLinks = array(
        array('Back to team', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">New team member</p>
            <h1>Add team member</h1>

            <p class="admin-muted">
                The card appears on the public <em>Our Team</em> page once saved.
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
                            placeholder="e.g. Executive Director"
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
                    >
                    <span class="admin-field-help">
                        PNG, JPG, WEBP or GIF. Maximum 3 MB. Leave empty to use
                        the default EARG logo.
                    </span>
                </label>

                <div class="admin-checks">
                    <label>
                        <input
                            type="checkbox"
                            name="is_visible"
                            value="1"
                            <?= $data['is_visible'] === '1' ? 'checked' : '' ?>
                        >
                        Show on the Our Team page
                    </label>
                </div>

                <button type="submit">Save team member</button>

                <a class="admin-cancel-link" href="index.php">Cancel</a>
            </form>
        </section>
    </main>

</body>
</html>
