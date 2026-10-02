<?php

declare(strict_types=1);

/*
 * Contact form handler.
 *
 * This script receives the "Send a message" form from the About page,
 * validates the submitted data and stores it in the `contact_messages`
 * table. The admin dashboard then shows the count of unread messages.
 *
 * After saving (success or failure) the visitor is sent back to the
 * About page contact section with a small status flag in the URL so the
 * page can show a simple "Message sent / not sent" notice.
 */

require_once __DIR__ . '/admin/includes/db.php';

// If the request is not a POST (e.g. someone opens the URL directly),
// just send them back to the About page.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: about.html#contact-us');
    exit;
}

// Read and clean the submitted fields.
$fullName = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

// Basic server-side validation (the browser also checks required fields).
$errorMessage = '';

if ($fullName === '' || $email === '' || $message === '') {
    $errorMessage = 'Please fill in your name, email and message.';
} elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errorMessage = 'Please enter a valid email address.';
}

$status = 'error';

if ($errorMessage === '') {
    try {
        // Save the message into the contact_messages table.
        $pdo = db();

        $statement = $pdo->prepare("
            INSERT INTO contact_messages (full_name, email, subject, message)
            VALUES (:full_name, :email, NULL, :message)
        ");

        $statement->execute([
            ':full_name' => $fullName,
            ':email' => $email,
            ':message' => $message,
        ]);

        $status = 'sent';
    } catch (Throwable $exception) {
        // Log the real error server-side without showing details to visitors.
        error_log('Contact form error: ' . $exception->getMessage());
    }
}

// Redirect back to the contact section with a status flag in the URL.
header('Location: about.html?status=' . $status . '#contact-us');
exit;