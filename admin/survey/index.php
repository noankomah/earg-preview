<?php

declare(strict_types=1);

/**
 * Admin > Survey responses.
 *
 * Shows the number of responses, charts for each question (drawn by
 * admin/assets/survey-charts.js from stats.php), a list of individual
 * responses to open, the written answers, and a CSV export button.
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
    $dateWhere .= ' AND submitted_at >= :from';
    $dateParams[':from'] = $from . ' 00:00:00';
}

if ($to !== '') {
    $dateWhere .= ' AND submitted_at <= :to';
    $dateParams[':to'] = $to . ' 23:59:59';
}

$totalStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM survey_responses WHERE 1=1' . $dateWhere);
$totalStatement->execute($dateParams);
$total = (int) $totalStatement->fetch()['total'];

$responsesStatement = $pdo->prepare(
    'SELECT id, submitted_at FROM survey_responses WHERE 1=1' . $dateWhere . '
     ORDER BY submitted_at DESC, id DESC LIMIT 500'
);
$responsesStatement->execute($dateParams);
$responses = $responsesStatement->fetchAll();

// Written answers (short text and paragraph questions, plus "Other" notes).
$writtenAnswers = [];
$writtenKeys = [];

foreach (survey_questions() as $key => $question) {
    if ($question['type'] === 'text' || $question['type'] === 'textarea') {
        $writtenKeys[$key] = $question;
    }
}

foreach ($writtenKeys as $key => $question) {
    $statement = $pdo->prepare(
        'SELECT a.value AS value, r.submitted_at AS submitted_at
         FROM survey_answers a
         JOIN survey_responses r ON r.id = a.response_id
         WHERE a.question_key = :key AND a.value <> \'\'' . $dateWhere . '
         ORDER BY r.submitted_at DESC, r.id DESC LIMIT 200'
    );
    $statement->execute(array_merge([':key' => $key], $dateParams));

    $writtenAnswers[$key] = $statement->fetchAll();
}

// Any "Other (please specify)" notes (stored under "q11_other").
$otherStatement = $pdo->prepare(
    'SELECT a.value AS value, r.submitted_at AS submitted_at
     FROM survey_answers a
     JOIN survey_responses r ON r.id = a.response_id
     WHERE a.question_key = :key AND a.value <> \'\'' . $dateWhere . '
     ORDER BY r.submitted_at DESC, r.id DESC LIMIT 200'
);
$otherStatement->execute(array_merge([':key' => 'q11_other'], $dateParams));
$otherAnswers = $otherStatement->fetchAll();

$exportQuery = http_build_query(array_filter([
    'export' => 'csv',
    'from' => $from,
    'to' => $to,
]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Responses | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="../assets/survey-admin.css">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Survey Responses';
    $adminLinks = array(
        array('Dashboard', '../dashboard.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">

        <section class="admin-page-head">
            <div>
                <p class="admin-eyebrow">Mentorship &amp; career readiness survey</p>
                <h1>Survey responses</h1>
                <p class="admin-muted">
                    <?= $total ?> response<?= $total === 1 ? '' : 's' ?>
                    <?php if ($from !== '' || $to !== ''): ?>
                        (<?= e($from !== '' ? $from : 'the beginning') ?> to <?= e($to !== '' ? $to : 'today') ?>)
                    <?php endif; ?>
                </p>
            </div>

            <a class="admin-primary-link" href="export.php?<?= e($exportQuery) ?>">
                Download CSV
            </a>
        </section>

        <form class="survey-filter" method="get" action="index.php">
            <label>From
                <input type="date" name="from" value="<?= e($from) ?>">
            </label>
            <label>To
                <input type="date" name="to" value="<?= e($to) ?>">
            </label>
            <button type="submit">Filter</button>
            <?php if ($from !== '' || $to !== ''): ?>
                <a href="index.php">Clear</a>
            <?php endif; ?>
        </form>

        <section class="admin-card-grid survey-stat-grid">
            <div class="admin-stat-card">
                <span><?= $total ?></span>
                <h2>Total responses</h2>
                <p><?= count($responses) ?> listed below<?= $total > count($responses) ? ' (showing the most recent 500)' : '' ?>.</p>
            </div>
        </section>

        <!-- Charts are drawn here by admin/assets/survey-charts.js. -->
        <section id="surveyCharts" class="survey-charts" data-from="<?= e($from) ?>" data-to="<?= e($to) ?>">
            <p class="admin-muted">Loading charts...</p>
        </section>

        <section class="admin-table-card">
            <h2 class="survey-section-title">Individual responses</h2>

            <?php if (!$responses): ?>
                <p class="admin-empty">No responses yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $response): ?>
                            <tr>
                                <td><?= (int) $response['id'] ?></td>
                                <td><?= e(date('j M Y, g:ia', strtotime((string) $response['submitted_at']))) ?></td>
                                <td>
                                    <a class="admin-inline-link" href="view.php?id=<?= (int) $response['id'] ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-table-card">
            <h2 class="survey-section-title">Written answers</h2>

            <?php
            $writtenSections = $writtenAnswers;
            if ($otherAnswers) {
                $writtenSections['q11_other'] = $otherAnswers;
            }
            ?>

            <?php if (empty($writtenSections)): ?>
                <p class="admin-empty">Nothing written yet.</p>
            <?php else: ?>
                <?php foreach ($writtenSections as $key => $answers): ?>
                    <div class="survey-written-block">
                        <h3>
                            <?php if ($key === 'q11_other'): ?>
                                11. Other barrier (please specify)
                            <?php else: ?>
                                <?= (int) $writtenKeys[$key]['number'] ?>. <?= e($writtenKeys[$key]['title']) ?>
                            <?php endif; ?>
                        </h3>

                        <?php if (!$answers): ?>
                            <p class="admin-empty">No answers yet.</p>
                        <?php else: ?>
                            <ul class="survey-written-list">
                                <?php foreach ($answers as $answer): ?>
                                    <li>
                                        <?= e((string) $answer['value']) ?>
                                        <span class="survey-written-date"><?= e(date('j M Y', strtotime((string) $answer['submitted_at']))) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script src="../../assets/vendor/chartjs/chart.umd.min.js"></script>
    <script src="../assets/survey-charts.js"></script>
</body>
</html>
