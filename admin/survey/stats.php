<?php

declare(strict_types=1);

/**
 * Returns the survey analytics as JSON for the dashboard charts.
 *
 * Called by admin/assets/survey-charts.js. Accepts optional ?from=YYYY-MM-DD
 * and ?to=YYYY-MM-DD to limit the figures to a date range.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

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

$from = survey_valid_date($_GET['from'] ?? '');
$to = survey_valid_date($_GET['to'] ?? '');

// Shared date filter, applied through a join to survey_responses.
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

try {
    $pdo = db();

    $totalStatement = $pdo->prepare(
        'SELECT COUNT(*) AS total FROM survey_responses r WHERE 1=1' . $dateWhere
    );
    $totalStatement->execute($dateParams);
    $total = (int) $totalStatement->fetch()['total'];

    $questions = [];

    foreach (survey_questions() as $key => $question) {
        $type = $question['type'];
        $entry = [
            'key' => $key,
            'number' => (int) $question['number'],
            'title' => $question['title'],
            'type' => $type,
        ];

        if ($type === 'radio' || $type === 'checkbox') {
            // Count how many times each option was chosen.
            $statement = $pdo->prepare(
                'SELECT a.value AS value, COUNT(*) AS total
                 FROM survey_answers a
                 JOIN survey_responses r ON r.id = a.response_id
                 WHERE a.question_key = :key' . $dateWhere . '
                 GROUP BY a.value'
            );
            $statement->execute(array_merge([':key' => $key], $dateParams));

            $counts = [];
            foreach ($statement->fetchAll() as $row) {
                $counts[(string) $row['value']] = (int) $row['total'];
            }

            $labels = $question['options'];
            if (!empty($question['other'])) {
                $labels[] = 'Other';
            }

            $options = [];
            foreach ($labels as $label) {
                $options[] = [
                    'label' => $label,
                    'count' => $counts[$label] ?? 0,
                ];
                unset($counts[$label]);
            }

            // Include any stray values that were not in the defined list.
            foreach ($counts as $label => $count) {
                $options[] = ['label' => $label, 'count' => $count];
            }

            $entry['options'] = $options;
        } elseif ($type === 'scale') {
            $statement = $pdo->prepare(
                'SELECT a.value AS value, COUNT(*) AS total
                 FROM survey_answers a
                 JOIN survey_responses r ON r.id = a.response_id
                 WHERE a.question_key = :key' . $dateWhere . '
                 GROUP BY a.value'
            );
            $statement->execute(array_merge([':key' => $key], $dateParams));

            $counts = [];
            foreach ($statement->fetchAll() as $row) {
                $counts[(int) $row['value']] = (int) $row['total'];
            }

            $values = [];
            $answered = 0;
            $sum = 0;

            for ($value = (int) $question['min']; $value <= (int) $question['max']; $value++) {
                $count = $counts[$value] ?? 0;
                $values[] = ['value' => $value, 'count' => $count];
                $answered += $count;
                $sum += $value * $count;
            }

            $entry['values'] = $values;
            $entry['answered'] = $answered;
            $entry['average'] = $answered > 0 ? round($sum / $answered, 2) : null;
            $entry['min_label'] = $question['min_label'];
            $entry['max_label'] = $question['max_label'];
        } else {
            // Free-text questions: report how many people wrote something.
            $statement = $pdo->prepare(
                "SELECT COUNT(*) AS total
                 FROM survey_answers a
                 JOIN survey_responses r ON r.id = a.response_id
                 WHERE a.question_key = :key AND a.value <> ''" . $dateWhere
            );
            $statement->execute(array_merge([':key' => $key], $dateParams));

            $entry['answered'] = (int) $statement->fetch()['total'];
        }

        $questions[] = $entry;
    }

    echo json_encode([
        'success' => true,
        'total' => $total,
        'from' => $from,
        'to' => $to,
        'questions' => $questions,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log('Survey stats error: ' . $exception->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to build the survey statistics right now.',
        'questions' => [],
    ]);
}
