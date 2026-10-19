<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/board_helpers.php';

require_admin();

$pdo = db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(404);
    exit('Board member not found.');
}

$member = null;
$loadError = null;

try {
    $statement = $pdo->prepare(
        "
        SELECT id, name, role_title, photo_url
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
    error_log('Admin board delete load error: ' . $exception->getMessage());

    $loadError = board_error_message($exception);
}

if (!$member && !$loadError) {
    http_response_code(404);
    exit('Board member not found.');
}

$errors = [];

// The delete button is a POST with a CSRF token, so a link on another site
// can never remove a member. After the row is gone we go back to the list
// with a success message (Post/Redirect/Get).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $member) {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if (!$errors) {
        try {
            $delete = $pdo->prepare(
                "
                DELETE FROM board_members
                WHERE id = :id
                "
            );

            $delete->execute([
                ':id' => $id,
            ]);
        } catch (Throwable $exception) {
            error_log('Admin board delete error: ' . $exception->getMessage());

            $errors[] = board_error_message($exception);
        }

        if (!$errors) {
            // Only now that the row is gone is the photo file safe to remove.
            delete_board_image((string) ($member['photo_url'] ?? ''));

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
    <title>Delete Board Member | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Delete Board Member';
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
                <h1>Cannot delete this member</h1>

                <div class="admin-alert">
                    <p><?= e($loadError) ?></p>
                </div>

                <a class="admin-cancel-link" href="index.php">Back to board</a>
            </section>
        <?php else: ?>
            <section class="admin-form-card">
                <p class="admin-eyebrow">Delete board member</p>
                <h1><?= e((string) $member['name']) ?></h1>

                <p class="admin-muted">
                    Role: <?= $member['role_title'] !== null && $member['role_title'] !== ''
                        ? e((string) $member['role_title'])
                        : 'Not set'
                    ?>
                </p>

                <?php if ($errors): ?>
                    <div class="admin-alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?= e($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="admin-alert">
                    <p>
                        This action cannot be undone. The member will be removed
                        from the website permanently.
                    </p>
                </div>

                <form method="post" class="admin-form">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <button type="submit" class="admin-danger-button">
                        Delete board member
                    </button>
                </form>
            </section>
        <?php endif; ?>
    </main>

</body>
</html>
