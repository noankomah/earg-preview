<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_admin();

$pdo = db();

// Handle actions: mark as read, mark as unread, archive, delete.
// Each action is a small POST with a CSRF token for safety.
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $notice = 'Security check failed. Please try again.';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $action = (string) ($_POST['action'] ?? '');

        if ($id > 0) {
            switch ($action) {
                case 'read':
                    $pdo->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = :id")
                        ->execute([':id' => $id]);
                    break;

                case 'unread':
                    $pdo->prepare("UPDATE contact_messages SET status = 'New' WHERE id = :id")
                        ->execute([':id' => $id]);
                    break;

                case 'archive':
                    $pdo->prepare("UPDATE contact_messages SET status = 'Archived' WHERE id = :id")
                        ->execute([':id' => $id]);
                    break;

                case 'delete':
                    $pdo->prepare("DELETE FROM contact_messages WHERE id = :id")
                        ->execute([':id' => $id]);
                    break;
            }

            $notice = 'Message updated.';
        }
    }
}

// Show current messages. "New" first, then "Read", then "Archived".
$messages = $pdo
    ->query("
        SELECT id, full_name, email, subject, message, status, created_at
        FROM contact_messages
        ORDER BY
            CASE status
                WHEN 'New' THEN 0
                WHEN 'Read' THEN 1
                ELSE 2
            END,
            created_at DESC
    ")
    ->fetchAll();

function format_message_time(?string $value): string
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
    <title>Messages | EA Research Group</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">

    <header class="admin-topbar">
        <div>
            <strong>EA Research Group</strong>
            <span>Messages Inbox</span>
        </div>

        <nav class="admin-topnav">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Log out</a>
        </nav>
    </header>

    <main class="admin-dashboard">
        <section class="admin-page-head">
            <div>
                <p class="admin-eyebrow">Contact form</p>
                <h1>Messages from the website</h1>
                <p class="admin-muted">
                    Messages sent through the contact form on the About page.
                </p>
            </div>
        </section>

        <?php if ($notice): ?>
            <div class="admin-success">
                <?= e($notice) ?>
            </div>
        <?php endif; ?>

        <section class="admin-table-card">
            <?php if (!$messages): ?>
                <p class="admin-empty">No messages yet.</p>
            <?php else: ?>
                <div class="admin-message-list">
                    <?php foreach ($messages as $message): ?>
                        <article
                            class="admin-message <?= $message['status'] === 'New' ? 'is-new' : '' ?>"
                        >
                            <div class="admin-message-head">
                                <div>
                                    <strong><?= e((string) $message['full_name']) ?></strong>
                                    <span class="admin-message-email">
                                        <?= e((string) $message['email']) ?>
                                    </span>
                                    <span class="admin-message-time">
                                        <?= e(format_message_time($message['created_at'])) ?>
                                    </span>
                                </div>

                                <span class="admin-message-status">
                                    <?= e((string) $message['status']) ?>
                                </span>
                            </div>

                            <?php if (!empty($message['subject'])): ?>
                                <p class="admin-message-subject">
                                    <?= e((string) $message['subject']) ?>
                                </p>
                            <?php endif; ?>

                            <p class="admin-message-body">
                                <?= nl2br(e((string) $message['message'])) ?>
                            </p>

                            <div class="admin-message-actions">
                                <?php if ($message['status'] === 'New'): ?>
                                    <form method="post" class="admin-inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                        <input type="hidden" name="action" value="read">
                                        <button type="submit">Mark as read</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" class="admin-inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                        <input type="hidden" name="action" value="unread">
                                        <button type="submit">Mark as unread</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($message['status'] !== 'Archived'): ?>
                                    <form method="post" class="admin-inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                        <input type="hidden" name="action" value="archive">
                                        <button type="submit">Archive</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" class="admin-inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="danger" onclick="return confirm('Delete this message permanently?')">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>