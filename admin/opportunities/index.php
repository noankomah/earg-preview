<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = db();

$opportunities = $pdo
    ->query("
        SELECT id, title, category, deadline, status, is_published, is_featured, created_at
        FROM opportunities
        ORDER BY created_at DESC
    ")
    ->fetchAll();

$notice = '';

if (isset($_GET['created'])) {
    $notice = 'Opportunity created successfully.';
} elseif (isset($_GET['updated'])) {
    $notice = 'Opportunity updated successfully.';
} elseif (isset($_GET['deleted'])) {
    $notice = 'Opportunity deleted successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunities | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Opportunities Manager';
    $adminLinks = array(
        array('Dashboard', '../dashboard.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-page-head">
            <div>
                <p class="admin-eyebrow">Publications</p>
                <h1>Scholarship & Opportunities</h1>
                <p class="admin-muted">
                    Add, edit and manage opportunities that will later appear on the public website.
                </p>
            </div>

            <a class="admin-button" href="create.php">Add opportunity</a>
        </section>

        <?php if ($notice): ?>
            <div class="admin-success">
                <?= e($notice) ?>
            </div>
        <?php endif; ?>

        <section class="admin-table-card">
            <?php if (!$opportunities): ?>
                <p class="admin-empty">No opportunities have been added yet.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Visibility</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($opportunities as $item): ?>
                                <tr>
                                    <td><?= e((string) $item['title']) ?></td>
                                    <td><?= e((string) $item['category']) ?></td>
                                    <td><?= e((string) ($item['deadline'] ?? 'Not set')) ?></td>
                                    <td><?= e((string) $item['status']) ?></td>
                                    <td>
                                        <?= ((int) $item['is_published'] === 1) ? 'Published' : 'Hidden' ?>
                                    </td>
                                    <td>
                                        <?= ((int) $item['is_featured'] === 1) ? 'Yes' : 'No' ?>
                                    </td>
                                    <td class="admin-actions">
                                        <a href="edit.php?id=<?= (int) $item['id'] ?>">Edit</a>
                                        <a class="danger" href="delete.php?id=<?= (int) $item['id'] ?>">Delete</a>
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
