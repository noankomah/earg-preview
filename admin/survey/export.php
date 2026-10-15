<?php

declare(strict_types=1);

/**
 * Admin > Survey responses > download as CSV.
 *
 * Produces one row per response, with a column for every question
 * (checkbox answers are joined with "; "). Opens directly in Excel.
 * Accepts the same ?from / ?to date range as the dashboard.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/survey_questions.php';

require_admin();

/**
 * Returns a safe "YYYY-MM-DD" date or an empty string if it is not valid.
 */
function survey_valid_date(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    return ($date && $date->format('Y-m-d') === $value) ? $value : '';
}

$pdo = db();

$from = survey_valid_date($_GET['from'] ?? '');
$to = survey_valid_date($_GET['to'] ?? '');

$dateWhere = '';
$dateParams = [];

if ($from !== '') {
    $dateWhere .= ' AND r.submitted_at >= :from';
    $dateParams[':from'] = $from . ' 00:00:00';
}

if ($to !== '') {
    $dateWhere .= ' AND r.submitted_at <= :to';
    $dateParams[':to'] = $to . ' 23:59:59';
}

$responseStatement = $pdo->prepare(
    'SELECT id, submitted_at FROM survey_responses r WHERE 1=1' . $dateWhere . '
     ORDER BY r.submitted_at ASC, r.id ASC'
);
$responseStatement->execute($dateParams);
$responses = $responseStatement->fetchAll();

// Pull every answer in one query and group them by response id.
$answersByResponse = [];

if ($responses) {
    $answerStatement = $pdo->prepare(
        'SELECT a.response_id, a.question_key, a.value
         FROM survey_answers a
         JOIN survey_responses r ON r.id = a.response_id
         WHERE 1=1' . $dateWhere . '
         ORDER BY a.response_id, a.id'
    );
    $answerStatement->execute($dateParams);

    foreach ($answerStatement->fetchAll() as $row) {
        $answersByResponse[(int) $row['response_id']][(string) $row['question_key']][] = (string) $row['value'];
    }
}

$questions = survey_questions();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="survey-responses-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Byte-order mark so Excel detects UTF-8 (needed for – and ’ characters).
fwrite($output, "\xEF\xBB\xBF");

$header = ['Response', 'Submitted at'];
foreach ($questions as $key => $question) {
    $header[] = $question['number'] . '. ' . $question['title'];
}
$header[] = '11. Other barrier (please specify)';

fputcsv($output, $header);

foreach ($responses as $response) {
    $responseId = (int) $response['id'];
    $row = [$responseId, (string) $response['submitted_at']];

    foreach ($questions as $key => $question) {
        $values = $answersByResponse[$responseId][$key] ?? [];
        $row[] = implode('; ', $values);
    }

    $other = $answersByResponse[$responseId]['q11_other'] ?? [];
    $row[] = implode('; ', $other);

    fputcsv($output, $row);
}

fclose($output);
exit;
