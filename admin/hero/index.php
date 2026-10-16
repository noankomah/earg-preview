<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/hero_helpers.php';

require_admin();

$pdo = db();

$slides = [];
$loadError = null;

// The hero table is newer than the rest of the schema, so a database that has
// not had admin/hero/schema.sql imported yet must show a clear message instead
// of a 500 error page.
try {
    $statement = $pdo->query(
        "
        SELECT
            id,
            image_url,
            position,
            display_order,
            is_visible
        FROM hero_slides
        ORDER BY display_order ASC, id ASC
        "
    );

    $slides = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log('Admin hero list error: ' . $exception->getMessage());

    $loadError = hero_error_message($exception);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Slider | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css?v=6">
</head>

<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Hero Slider';
    $adminLinks = array(
        array('Dashboard', '../dashboard.php'),
        array('View on website', '../../index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">

        <section class="admin-page-head">
            <div>
                <p class="admin-eyebrow">Home page</p>
                <h1>Hero slider</h1>
                <p class="admin-muted">
                    The full-width photos that rotate at the top of the home page.
                </p>
            </div>

            <a class="admin-button" href="create.php">
                Add slide
            </a>
        </section>

        <?php if (isset($_GET['created'])): ?>
            <div class="admin-success">
                <p>Slide added successfully.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="admin-success">
                <p>Slide updated successfully.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="admin-success">
                <p>Slide deleted successfully.</p>
            </div>
        <?php endif; ?>

        <?php if ($loadError): ?>
            <div class="admin-alert">
                <p><?= e($loadError) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$loadError && !$slides): ?>
            <section class="admin-table-card">
                <div class="admin-empty">
                    <h2>No slides yet</h2>
                    <p>
                        Add a slide to start the home page slider. Until then the
                        home page shows a plain green background.
                    </p>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($slides): ?>
            <section class="admin-table-card">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Position</th>
                                <th>Order</th>
                                <th>Shown</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($slides as $slide): ?>
                                <tr>
                                    <td>
                                        <img
                                            class="admin-table-photo"
                                            src="../../<?= e((string) $slide['image_url']) ?>"
                                            alt=""
                                        >
                                    </td>

                                    <td><?= e((string) $slide['position']) ?></td>

                                    <td><?= (int) $slide['display_order'] ?></td>

                                    <td>
                                        <?= (int) $slide['is_visible'] === 1 ? 'Yes' : 'No' ?>
                                    </td>

                                    <td>
                                        <div class="admin-actions">
                                            <a href="edit.php?id=<?= (int) $slide['id'] ?>">
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= (int) $slide['id'] ?>"
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
