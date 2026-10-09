<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = db();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT id, title, category, deadline
    FROM opportunities
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id,
]);

$opportunity = $stmt->fetch();

if (!$opportunity) {
    http_response_code(404);
    echo 'Opportunity not found.';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Security check failed. Please try again.';
    } else {
        $delete = $pdo->prepare("
            DELETE FROM opportunities
            WHERE id = :id
        ");

        $delete->execute([
            ':id' => $id,
        ]);

        header('Location: index.php?deleted=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Opportunity | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Delete Opportunity';
    $adminLinks = array(
        array('Back to opportunities', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-form-card danger-zone">
            <p class="admin-eyebrow">Confirm delete</p>
            <h1><?= e((string) $opportunity['title']) ?></h1>
            <p class="admin-muted">
                This will permanently delete this opportunity from the database.
            </p>

            <?php if ($error): ?>
                <div class="admin-alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="admin-delete-summary">
                <p><strong>Category:</strong> <?= e((string) $opportunity['category']) ?></p>
                <p><strong>Deadline:</strong> <?= e((string) ($opportunity['deadline'] ?? 'Not set')) ?></p>
            </div>

            <form method="post" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <button type="submit" class="admin-danger-button">Yes, delete opportunity</button>
                <a class="admin-cancel-link" href="index.php">Cancel</a>
            </form>
        </section>
    </main>

</body>
</html>
