<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(404);
    exit('Publication not found.');
}

$statement = $pdo->prepare(
    "
    SELECT
        id,
        title,
        publication_type
    FROM publications
    WHERE id = :id
    LIMIT 1
    "
);

$statement->execute([
    ':id' => $id,
]);

$publication = $statement->fetch();

if (!$publication) {
    http_response_code(404);
    exit('Publication not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if (!$errors) {
        $delete = $pdo->prepare(
            "
            DELETE FROM publications
            WHERE id = :id
            "
        );

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
    <title>Delete Publication | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body class="admin-body">

    <header class="admin-topbar">
        <div>
            <strong>EA Research Group</strong>
            <span>Delete Publication</span>
        </div>

        <nav class="admin-topnav">
            <a href="index.php">Back to publications</a>
            <a href="../logout.php">Log out</a>
        </nav>
    </header>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">Delete publication</p>
            <h1><?= e((string) $publication['title']) ?></h1>

            <p class="admin-muted">
                Type: <?= e((string) $publication['publication_type']) ?>
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
                    This action cannot be undone. The publication will be removed permanently.
                </p>
            </div>

            <form method="post" class="admin-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <button type="submit" class="admin-danger-button">
                    Delete publication
                </button>
            </form>
        </section>
    </main>

</body>
</html>
