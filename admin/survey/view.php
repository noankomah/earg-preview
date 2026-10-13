<?php

declare(strict_types=1);

/**
 * Admin > Survey responses > view one response.
 *
 * Shows every answer for a single submission, in question order.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/survey_questions.php';

require_admin();

$pdo = db();

$id = (int) ($_GET['id'] ?? 0);

$responseStatement = $pdo->prepare('SELECT id, submitted_at FROM survey_responses WHERE id = :id');
$responseStatement->execute([':id' => $id]);
$response = $responseStatement->fetch();

$answers = [];

if ($response) {
    $answerStatement = $pdo->prepare(
        'SELECT question_key, value FROM survey_answers WHERE response_id = :id ORDER BY id'
    );
    $answerStatement->execute([':id' => $id]);

    foreach ($answerStatement->fetchAll() as $row) {
        $answers[(string) $row['question_key']][] = (string) $row['value'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Response #<?= (int) $id ?> | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="../assets/survey-admin.css">
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Survey Response';
    $adminLinks = array(
        array('All responses', 'index.php'),
        array('Dashboard', '../dashboard.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">

        <?php if (!$response): ?>
            <section class="admin-page-head">
                <div>
                    <p class="admin-eyebrow">Survey responses</p>
                    <h1>Response not found</h1>
                    <p class="admin-muted">That response does not exist.</p>
                </div>
            </section>

            <p><a class="admin-inline-link" href="index.php">Back to all responses</a></p>
        <?php else: ?>
            <section class="admin-page-head">
                <div>
                    <p class="admin-eyebrow">Survey response</p>
                    <h1>Response #<?= (int) $response['id'] ?></h1>
                    <p class="admin-muted">
                        Submitted <?= e(date('j M Y, g:ia', strtotime((string) $response['submitted_at']))) ?>
                    </p>
                </div>

                <a class="admin-primary-link" href="index.php">Back to all responses</a>
            </section>

            <section class="admin-table-card">
                <div>
                    <?php foreach (survey_questions() as $key => $question): ?>
                        <div class="survey-answer-row">
                            <p class="survey-answer-q">
                                <?= (int) $question['number'] ?>. <?= e($question['title']) ?>
                            </p>

                            <div class="survey-answer-a">
                                <?php if (!empty($answers[$key])): ?>
                                    <?php if ($question['type'] === 'checkbox'): ?>
                                        <ul>
                                            <?php foreach ($answers[$key] as $value): ?>
                                                <li><?= e($value) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <?= e($answers[$key][0]) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="admin-muted">No answer</span>
                                <?php endif; ?>

                                <?php if ($key === 'q11' && !empty($answers['q11_other'])): ?>
                                    <p class="survey-answer-other">
                                        Other: <?= e(implode('; ', $answers['q11_other'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

</body>
</html>
