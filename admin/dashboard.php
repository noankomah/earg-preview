<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_admin();

$admin = current_admin();

$pdo = db();

$stats = [
    'opportunities' => 0,
    'publications' => 0,
    'messages' => 0,
    'subscribers' => 0,
    'founder_story' => 0,
];

$stats['opportunities'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM opportunities")
    ->fetch()['total'];

$stats['publications'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM publications")
    ->fetch()['total'];

$stats['messages'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'New'")
    ->fetch()['total'];

$stats['subscribers'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM newsletter_subscribers")
    ->fetch()['total'];

$stats['founder_story'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM founder_story")
    ->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | EA Research Group</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Admin Dashboard';
    $adminLinks = array(
        array('Log out', 'logout.php'),
    );
    ?>
    <?php include __DIR__ . '/includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-welcome">
            <p class="admin-eyebrow">Welcome</p>
            <h1><?= e((string) $admin['full_name']) ?></h1>
            <p class="admin-muted">
                Role: <?= e((string) $admin['role']) ?>
            </p>
        </section>

        <section class="admin-card-grid">
            <a class="admin-stat-card admin-stat-card-link" href="opportunities/index.php">
                <span><?= $stats['opportunities'] ?></span>
                <h2>Opportunities</h2>
                <p>Add, edit and manage scholarships, fellowships, internships and calls.</p>
            </a>

            <a class="admin-stat-card admin-stat-card-link" href="messages.php">
                <span><?= $stats['messages'] ?></span>
                <h2>New messages</h2>
                <p>Unread contact messages from the public website.</p>
            </a>

            <a class="admin-stat-card admin-stat-card-link" href="subscribers.php">
                <span><?= $stats['subscribers'] ?></span>
                <h2>Newsletter subscribers</h2>
                <p>Emails signed up through the home page newsletter box.</p>
            </a>

            <a class="admin-stat-card admin-stat-card-link" href="founder_story.php">
                <span><?= $stats['founder_story'] ?></span>
                <h2>Founder story</h2>
                <p>Edit the founder's story shown on the public website.</p>
            </a>

           <a
                class="admin-stat-card admin-stat-card-link"
                href="publications/index.php"
            >
                <span><?= $stats['publications'] ?></span>
                <h2>Publications</h2>
                <p>Add, edit and manage blogs, newsletters and reports.</p>
            </a>
        </section>
    </main>

</body>
</html>
