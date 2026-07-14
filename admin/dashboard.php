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
];

$stats['opportunities'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM opportunities")
    ->fetch()['total'];

$stats['messages'] = (int) $pdo
    ->query("SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'New'")
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

    <header class="admin-topbar">
        <div>
            <strong>EA Research Group</strong>
            <span>Admin Dashboard</span>
        </div>

        <a href="logout.php">Log out</a>
    </header>

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

            <article class="admin-stat-card">
                <span><?= $stats['messages'] ?></span>
                <h2>New messages</h2>
                <p>Unread contact messages from the public website.</p>
            </article>

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
