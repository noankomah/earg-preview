<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if (!csrf_is_valid($csrf)) {
        $error = 'Unable to sign in.';
    } elseif ($email === '' || $password === '') {
        $error = 'Unable to sign in.';
    } else {
        $pdo = db();

        $attemptCheck = $pdo->prepare("
            SELECT COUNT(*) AS failed_count
            FROM login_attempts
            WHERE was_successful = 0
              AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
              AND (identifier = :identifier OR ip_address = :ip_address)
        ");

        $attemptCheck->execute([
            ':identifier' => $email,
            ':ip_address' => $ipAddress,
        ]);

        $failedCount = (int) $attemptCheck->fetch()['failed_count'];

        if ($failedCount >= 5) {
            $logAttempt = $pdo->prepare("
                INSERT INTO login_attempts (identifier, ip_address, was_successful)
                VALUES (:identifier, :ip_address, 0)
            ");

            $logAttempt->execute([
                ':identifier' => $email,
                ':ip_address' => $ipAddress,
            ]);

            $error = 'Unable to sign in.';
        } else {
            $stmt = $pdo->prepare("
                SELECT id, full_name, email, password_hash, role
                FROM admin_users
                WHERE email = :email
                  AND is_active = 1
                LIMIT 1
            ");

            $stmt->execute([
                ':email' => $email,
            ]);

            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $logAttempt = $pdo->prepare("
                    INSERT INTO login_attempts (identifier, ip_address, was_successful)
                    VALUES (:identifier, :ip_address, 1)
                ");

                $logAttempt->execute([
                    ':identifier' => $email,
                    ':ip_address' => $ipAddress,
                ]);

                $updateLogin = $pdo->prepare("
                    UPDATE admin_users
                    SET last_login_at = NOW()
                    WHERE id = :id
                ");

                $updateLogin->execute([
                    ':id' => $admin['id'],
                ]);

                login_admin($admin);

                header('Location: dashboard.php');
                exit;
            }

            $logAttempt = $pdo->prepare("
                INSERT INTO login_attempts (identifier, ip_address, was_successful)
                VALUES (:identifier, :ip_address, 0)
            ");

            $logAttempt->execute([
                ':identifier' => $email,
                ':ip_address' => $ipAddress,
            ]);

            $error = 'Unable to sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | EA Research Group</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">

    <main class="admin-login-shell">
        <section class="admin-login-card">
            <p class="admin-eyebrow">EA Research Group</p>
            <h1>Admin Access</h1>
            <p class="admin-muted">
                Sign in to manage opportunities and website updates.
            </p>

            <?php if ($error): ?>
                <div class="admin-alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php" class="admin-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label>
                    Email address
                    <input type="email" name="email" required>
                </label>

                <label>
                    Password
                    <input type="password" name="password" required>
                </label>

                <button type="submit">Sign in</button>
            </form>
        </section>
    </main>

</body>
</html>