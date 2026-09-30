<?php

declare(strict_types=1);

/*
 * Newsletter sign-up handler.
 *
 * This script receives the homepage newsletter form (submitted with
 * JavaScript via fetch), validates the email address and stores it in
 * the `newsletter_subscribers` table. Duplicate emails are ignored by
 * the unique index, so the same address is only saved once.
 *
 * It responds with a small JSON message the homepage JS shows to the
 * visitor ("Thanks for subscribing!" or something went wrong).
 */

require_once __DIR__ . '/admin/includes/db.php';

// This endpoint only accepts POST requests (from the newsletter form).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.',
    ]);

    exit;
}

// Read the email address from the form and validate it.
$email = strtolower(trim((string) ($_POST['email'] ?? '')));

if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    http_response_code(422);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.',
    ]);

    exit;
}

try {
    // Save the email. "INSERT IGNORE" means repeated sign-ups do not error.
    $pdo = db();

    $statement = $pdo->prepare("
        INSERT IGNORE INTO newsletter_subscribers (email)
        VALUES (:email)
    ");

    $statement->execute([
        ':email' => $email,
    ]);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing!',
    ]);
} catch (Throwable $exception) {
    error_log('Newsletter sign-up error: ' . $exception->getMessage());

    http_response_code(500);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Unable to subscribe right now. Please try again later.',
    ]);
}