<?php

declare(strict_types=1);

/**
 * Shared helpers for the public survey.
 *
 * Used by survey.php (the form page) and api/survey.php (the endpoint that
 * validates and saves a submission). Keeping the validation and saving here
 * means the form and the endpoint can never disagree about the rules.
 */

require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/survey_questions.php';

/*
 * A fixed salt used before hashing a visitor's IP address. Hashing (instead
 * of storing the raw IP) keeps the data anonymous while still letting us
 * rate-limit obvious spam. It is not a secret key, just a fixed prefix.
 */
if (!defined('SURVEY_IP_SALT')) {
    define('SURVEY_IP_SALT', 'earg-survey-2026-ip');
}

/**
 * Starts a lightweight session for the public survey (separate from the
 * admin session so the two never clash). Used for the CSRF token, the
 * "one submission per browser" check and the submit-timing spam check.
 */
function survey_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('EARG_PUBLIC_SESSION');
    session_start();
}

/**
 * Returns (and lazily creates) the CSRF token for the survey form.
 */
function survey_csrf_token(): string
{
    survey_start_session();

    if (empty($_SESSION['survey_csrf_token'])) {
        $_SESSION['survey_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['survey_csrf_token'];
}

/**
 * Checks a submitted CSRF token against the one stored in the session.
 */
function survey_csrf_is_valid(?string $token): bool
{
    survey_start_session();

    return is_string($token)
        && isset($_SESSION['survey_csrf_token'])
        && hash_equals($_SESSION['survey_csrf_token'], $token);
}

/**
 * Hashes the visitor's IP address so we never store it in plain text.
 */
function survey_ip_hash(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    return hash('sha256', SURVEY_IP_SALT . '|' . $ip);
}

/**
 * Records the moment the form page was first shown. api/survey.php uses this
 * to reject submissions that arrive impossibly fast (a common bot tell).
 */
function survey_mark_started(): void
{
    survey_start_session();

    if (empty($_SESSION['survey_started_at'])) {
        $_SESSION['survey_started_at'] = time();
    }
}

/**
 * How many responses have already come from this IP hash in the last hour.
 * Used as a gentle rate limit for obvious spam floods.
 */
function survey_recent_ip_count(string $ipHash): int
{
    $pdo = db();

    $statement = $pdo->prepare(
        'SELECT COUNT(*) AS total FROM survey_responses
         WHERE ip_hash = :ip_hash
           AND submitted_at > (NOW() - INTERVAL 1 HOUR)'
    );

    $statement->execute([':ip_hash' => $ipHash]);

    return (int) $statement->fetch()['total'];
}

/**
 * Validates the raw form input against the question definitions.
 *
 * Returns:
 *   errors - question key => human-readable message, for anything invalid.
 *   rows   - flat list of [question_key, value] pairs ready to be inserted.
 *
 * Empty (blank) answers to optional questions are simply skipped.
 */
function survey_collect_answers(array $input): array
{
    $errors = [];
    $rows = [];

    foreach (survey_questions() as $key => $question) {
        $type = $question['type'];
        $required = !empty($question['required']);

        if ($type === 'radio') {
            $value = trim((string) ($input[$key] ?? ''));

            if ($value === '') {
                if ($required) {
                    $errors[$key] = 'Please choose an option.';
                }

                continue;
            }

            if (!in_array($value, $question['options'], true)) {
                $errors[$key] = 'Please choose one of the listed options.';

                continue;
            }

            $rows[] = [$key, $value];
            continue;
        }

        if ($type === 'checkbox') {
            $selected = $input[$key] ?? [];
            $selected = is_array($selected) ? $selected : [];

            // Only accept options we recognise (plus "Other" when allowed).
            $allowed = $question['options'];

            if (!empty($question['other'])) {
                $allowed[] = 'Other';
            }

            $clean = [];

            foreach ($selected as $choice) {
                $choice = trim((string) $choice);

                if ($choice !== '' && in_array($choice, $allowed, true) && !in_array($choice, $clean, true)) {
                    $clean[] = $choice;
                }
            }

            if (!$clean && $required) {
                $errors[$key] = 'Please select at least one option.';
                continue;
            }

            foreach ($clean as $choice) {
                $rows[] = [$key, $choice];
            }

            // "Other" answers may carry a free-text explanation.
            if (!empty($question['other']) && in_array('Other', $clean, true)) {
                $otherText = trim((string) ($input[$key . '_other'] ?? ''));
                $maxLength = (int) ($question['other_maxlength'] ?? 300);

                if ($otherText === '') {
                    $errors[$key] = 'Please describe your "Other" answer.';
                } elseif (mb_strlen($otherText) > $maxLength) {
                    $errors[$key] = 'Your "Other" answer is too long.';
                } else {
                    $rows[] = [$key . '_other', $otherText];
                }
            }

            continue;
        }

        if ($type === 'text' || $type === 'textarea') {
            $value = trim((string) ($input[$key] ?? ''));

            if ($value === '') {
                if ($required) {
                    $errors[$key] = 'This question is required.';
                }

                continue;
            }

            $maxLength = (int) ($question['maxlength'] ?? 500);

            if (mb_strlen($value) > $maxLength) {
                $errors[$key] = 'Please keep your answer under ' . $maxLength . ' characters.';

                continue;
            }

            $rows[] = [$key, $value];
            continue;
        }

        if ($type === 'scale') {
            $value = trim((string) ($input[$key] ?? ''));

            if ($value === '') {
                if ($required) {
                    $errors[$key] = 'Please choose a value.';
                }

                continue;
            }

            if (!ctype_digit($value)) {
                $errors[$key] = 'Please choose a number from the scale.';

                continue;
            }

            $number = (int) $value;
            $min = (int) $question['min'];
            $max = (int) $question['max'];

            if ($number < $min || $number > $max) {
                $errors[$key] = 'Please choose a number between ' . $min . ' and ' . $max . '.';

                continue;
            }

            $rows[] = [$key, (string) $number];
            continue;
        }
    }

    return [
        'errors' => $errors,
        'rows' => $rows,
    ];
}

/**
 * Saves a validated set of answer rows as one response.
 *
 * Everything happens inside a transaction, so we never end up with a
 * response that has only half of its answers saved.
 */
function survey_store(array $rows, string $ipHash): int
{
    $pdo = db();

    $pdo->beginTransaction();

    try {
        $pdo->prepare('INSERT INTO survey_responses (ip_hash) VALUES (:ip_hash)')
            ->execute([':ip_hash' => $ipHash]);

        $responseId = (int) $pdo->lastInsertId();

        $insert = $pdo->prepare(
            'INSERT INTO survey_answers (response_id, question_key, value)
             VALUES (:response_id, :question_key, :value)'
        );

        foreach ($rows as $row) {
            $insert->execute([
                ':response_id' => $responseId,
                ':question_key' => $row[0],
                ':value' => $row[1],
            ]);
        }

        $pdo->commit();

        return $responseId;
    } catch (Throwable $exception) {
        $pdo->rollBack();

        throw $exception;
    }
}
