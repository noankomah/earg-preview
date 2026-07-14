<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = db();

$statement = $pdo->query(
    "
    SELECT
        id,
        title,
        slug,
        publication_type,
        author,
        publication_date,
        is_published,
        is_featured,
        created_at
    FROM publications
    ORDER BY created_at DESC
    "
);

$publications = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publications | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body class="admin-body">

    <header class="admin-topbar">
        <div>
            <strong>EA Research Group</strong>
            <span>Publications Manager</span>
        </div>

        <nav class="admin-topnav">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php">Log out</a>
        </nav>
    </header>

    <main class="admin-dashboard">

        <section class="admin-page-heading">
            <div>
                <p class="admin-eyebrow">Content manager</p>
                <h1>Publications</h1>
                <p class="admin-muted">
                    Manage blogs, newsletters and reports.
                </p>
            </div>

            <a class="admin-primary-link" href="create.php">
                Add publication
            </a>
        </section>

        <?php if (isset($_GET['created'])): ?>
            <div class="admin-success">
                Publication created successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="admin-success">
                Publication updated successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="admin-success">
                Publication deleted successfully.
            </div>
        <?php endif; ?>

        <section class="admin-table-card">
            <?php if (!$publications): ?>
                <div class="admin-empty">
                    <h2>No publications yet</h2>
                    <p>
                        Add your first blog, newsletter or report.
                    </p>
                </div>
            <?php else: ?>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Published</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($publications as $publication): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= e((string) $publication['title']) ?>
                                        </strong>

                                        <?php if (!empty($publication['author'])): ?>
                                            <small>
                                                By <?= e((string) $publication['author']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= e((string) $publication['publication_type']) ?>
                                    </td>

                                    <td>
                                        <?= $publication['publication_date']
                                            ? e(date(
                                                'j M Y',
                                                strtotime(
                                                    (string) $publication['publication_date']
                                                )
                                            ))
                                            : 'Not specified'
                                        ?>
                                    </td>

                                    <td>
                                        <?= (int) $publication['is_published'] === 1
                                            ? 'Yes'
                                            : 'No'
                                        ?>
                                    </td>

                                    <td>
                                        <?= (int) $publication['is_featured'] === 1
                                            ? 'Yes'
                                            : 'No'
                                        ?>
                                    </td>

                                    <td>
                                        <div class="admin-actions">
                                            <a
                                                href="edit.php?id=<?= (int) $publication['id'] ?>"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= (int) $publication['id'] ?>"
                                                class="admin-danger-link"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </section>

    </main>

</body>
</html>
