<?php

declare(strict_types=1);

/**
 * Public survey submission endpoint (called by assets/js/survey.js with fetch).
 *
 * It checks the CSRF token, screens out obvious bots (honeypot + timing),
 * validates every answer against includes/survey_questions.php, then saves
 * the response in a single transaction. It always replies with JSON.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../includes/survey_helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);

    exit;
}

survey_start_session();

$respond = static function (bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
};

try {
    /*
     * Honeypot: a hidden field real visitors never fill in. If a bot fills it
     * we reply as if everything worked so the bot does not try again.
     */
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        $respond(true, 'Thank you. Your response has been recorded.');

        exit;
    }

    // CSRF protection.
    if (!survey_csrf_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        $respond(false, 'Your session expired. Please reload the page and try again.');

        exit;
    }

    // Timing check: a form filled in under 3 seconds is almost always a bot.
    $startedAt = (int) ($_SESSION['survey_started_at'] ?? 0);

    if ($startedAt > 0 && (time() - $startedAt) < 3) {
        $respond(true, 'Thank you. Your response has been recorded.');

        exit;
    }

    // Stop the same browser sending the survey twice.
    if (!empty($_SESSION['survey_submitted'])) {
        http_response_code(409);
        $respond(false, 'It looks like you have already submitted a response from this browser.');

        exit;
    }

    // Validate every answer.
    $collected = survey_collect_answers($_POST);

    if (!empty($collected['errors'])) {
        http_response_code(422);
        $respond(false, 'Please check the highlighted questions and try again.', [
            'errors' => $collected['errors'],
        ]);

        exit;
    }

    // Gentle per-IP rate limit to blunt spam floods.
    $ipHash = survey_ip_hash();

    if (survey_recent_ip_count($ipHash) >= 10) {
        http_response_code(429);
        $respond(false, 'Too many responses have been submitted from your connection. Please try again later.');

        exit;
    }

    survey_store($collected['rows'], $ipHash);

    $_SESSION['survey_submitted'] = true;

    $respond(true, 'Thank you. Your response has been recorded.');
} catch (Throwable $exception) {
    error_log('Survey submission error: ' . $exception->getMessage());

    http_response_code(500);
    $respond(false, 'Something went wrong while saving your response. Please try again later.');
}
