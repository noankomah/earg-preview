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
    'survey' => 0,
    'team' => 0,
    'hero' => 0,
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

$stats['survey'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM survey_responses")
    ->fetch()['total'];

// The team table is newer than the rest of the schema. Until
// admin/team/schema.sql has been imported, the count simply stays at 0 so
// the dashboard never fails to load.
try {
    $stats['team'] = (int) $pdo
        ->query("SELECT COUNT(*) AS total FROM team_members")
        ->fetch()['total'];
} catch (Throwable $exception) {
    error_log('Dashboard team count error: ' . $exception->getMessage());
}

// Same idea for the hero slider table (admin/hero/schema.sql).
try {
    $stats['hero'] = (int) $pdo
        ->query("SELECT COUNT(*) AS total FROM hero_slides")
        ->fetch()['total'];
} catch (Throwable $exception) {
    error_log('Dashboard hero count error: ' . $exception->getMessage());
}
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

            <a class="admin-stat-card admin-stat-card-link" href="survey/index.php">
                <span><?= $stats['survey'] ?></span>
                <h2>Survey responses</h2>
                <p>View the charts, read answers and export the mentorship survey as CSV.</p>
            </a>

            <a class="admin-stat-card admin-stat-card-link" href="team/index.php">
                <span><?= $stats['team'] ?></span>
                <h2>Team members</h2>
                <p>Add, edit and reorder the people shown on the Our Team page.</p>
            </a>

            <a class="admin-stat-card admin-stat-card-link" href="hero/index.php">
                <span><?= $stats['hero'] ?></span>
                <h2>Hero slider</h2>
                <p>Add, edit and reorder the photos that rotate on the home page.</p>
            </a>
        </section>
    </main>

</body>
</html>
