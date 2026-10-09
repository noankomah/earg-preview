<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_admin();

$pdo = db();

// CSV export downloads all subscriber emails as a spreadsheet-friendly file.
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $subscribers = $pdo
        ->query("SELECT email, created_at FROM newsletter_subscribers ORDER BY created_at DESC")
        ->fetchAll();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="newsletter-subscribers-' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email', 'Subscribed At']);

    foreach ($subscribers as $subscriber) {
        fputcsv($output, [
            (string) $subscriber['email'],
            (string) $subscriber['created_at'],
        ]);
    }

    fclose($output);
    exit;
}

// Handle removing a single subscriber (POST with CSRF token).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $notice = 'Security check failed. Please try again.';
    } else {
        $id = (int) ($_POST['id'] ?? 0);

        $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = :id")
            ->execute([':id' => $id]);

        $notice = 'Subscriber removed.';
    }
}

$subscribers = $pdo
    ->query("SELECT id, email, created_at FROM newsletter_subscribers ORDER BY created_at DESC")
    ->fetchAll();

function format_subscriber_time(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return (string) $value;
    }

    return date('j M Y, g:ia', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribers | EA Research Group</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Newsletter Subscribers';
    $adminLinks = array(
        array('Dashboard', 'dashboard.php'),
        array('Log out', 'logout.php'),
    );
    ?>
    <?php include __DIR__ . '/includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-page-head">
            <div>
                <p class="admin-eyebrow">Home page newsletter</p>
                <h1>Newsletter subscribers</h1>
                <p class="admin-muted">
                    Email addresses that signed up to receive updates.
                </p>
            </div>

            <a class="admin-primary-link" href="subscribers.php?export=csv">
                Download CSV
            </a>
        </section>

        <?php if (!empty($notice)): ?>
            <div class="admin-success">
                <?= e($notice) ?>
            </div>
        <?php endif; ?>

        <section class="admin-table-card">
            <?php if (!$subscribers): ?>
                <p class="admin-empty">No subscribers yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Subscribed at</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?= e((string) $subscriber['email']) ?></td>
                                <td><?= e(format_subscriber_time($subscriber['created_at'])) ?></td>
                                <td>
                                    <form method="post" class="admin-inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $subscriber['id'] ?>">
                                        <button type="submit" class="danger" onclick="return confirm('Remove this subscriber?')">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>