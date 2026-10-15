<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/team_helpers.php';

require_admin();

$pdo = db();

$members = [];
$loadError = null;

// The team table is newer than the rest of the schema, so a database that
// has not had admin/team/schema.sql imported yet must show a clear message
// instead of a 500 error page.
try {
    $statement = $pdo->query(
        "
        SELECT
            id,
            name,
            role_title,
            photo_url,
            display_order,
            is_visible
        FROM team_members
        ORDER BY display_order ASC, id ASC
        "
    );

    $members = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log('Admin team list error: ' . $exception->getMessage());

    $loadError = team_error_message($exception);
}

// The public page falls back to this image when a member has no photo yet.
$defaultPhoto = 'assets/images/earg-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Team Manager';
    $adminLinks = array(
        array('Dashboard', '../dashboard.php'),
        array('View on website', '../views/About/our-team.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">

        <section class="admin-page-head">
            <div>
                <p class="admin-eyebrow">Content manager</p>
                <h1>Our Team</h1>
                <p class="admin-muted">
                    People shown on the public <em>Our Team</em> page, in card order.
                </p>
            </div>

            <a class="admin-primary-link" href="create.php">
                Add team member
            </a>
        </section>

        <?php if (isset($_GET['created'])): ?>
            <div class="admin-success">
                Team member added successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="admin-success">
                Team member updated successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="admin-success">
                Team member deleted successfully.
            </div>
        <?php endif; ?>

        <?php if ($loadError): ?>
            <div class="admin-alert">
                <p><?= e($loadError) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$loadError && !$members): ?>
            <section class="admin-table-card">
                <div class="admin-empty">
                    <h2>No team members yet</h2>
                    <p>
                        Add your first team member to show a card on the website.
                    </p>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($members): ?>
            <section class="admin-table-card">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Role / title</th>
                                <th>Order</th>
                                <th>Shown</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <img
                                            class="admin-table-photo"
                                            src="../<?= e((string) ($member['photo_url'] ?: $defaultPhoto)) ?>"
                                            alt=""
                                        >
                                    </td>

                                    <td>
                                        <strong><?= e((string) $member['name']) ?></strong>
                                    </td>

                                    <td>
                                        <?= $member['role_title'] !== null && $member['role_title'] !== ''
                                            ? e((string) $member['role_title'])
                                            : 'Not set'
                                        ?>
                                    </td>

                                    <td><?= (int) $member['display_order'] ?></td>

                                    <td>
                                        <?= (int) $member['is_visible'] === 1 ? 'Yes' : 'No' ?>
                                    </td>

                                    <td>
                                        <div class="admin-actions">
                                            <a href="edit.php?id=<?= (int) $member['id'] ?>">
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= (int) $member['id'] ?>"
                                                class="danger"
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
            </section>
        <?php endif; ?>

    </main>

</body>
</html>
