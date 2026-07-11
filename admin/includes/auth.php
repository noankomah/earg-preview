<?php

declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (($_SERVER['SERVER_PORT'] ?? '') === '443')
    );

    session_name('EARG_ADMIN_SESSION');

    /*
     * More compatible session cookie setup for cPanel PHP versions.
     * The previous array-style session_set_cookie_params can trigger
     * a 500 error on older PHP versions.
     */
    session_set_cookie_params(
        0,
        '/; SameSite=Lax',
        '',
        $isHttps,
        true
    );

    session_start();
}

start_secure_session();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_user'])) {
        return null;
    }

    return $_SESSION['admin_user'];
}

function require_admin(): void
{
    if (!current_admin()) {
        header('Location: index.php');
        exit;
    }
}

function login_admin(array $admin): void
{
    session_regenerate_id(true);

    $_SESSION['admin_user'] = [
        'id' => (int) $admin['id'],
        'full_name' => $admin['full_name'],
        'email' => $admin['email'],
        'role' => $admin['role'],
    ];
}

function logout_admin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'] ?? '',
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}