<?php

declare(strict_types=1);

/**
 * Admin > Survey responses > view one response.
 *
 * Shows every answer for a single submission, in question order.
 *
 * Styling uses Bootstrap (assets/vendor/bootstrap) on top of the shared
 * admin chrome (admin.css). The delete confirmation lives in
 * admin/assets/survey-admin.js.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/survey_questions.php';

require_admin();

$pdo = db();

// Delete this response (POST with CSRF token), then return to the list.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        header('Location: index.php?error=csrf');
        exit;
    }

    $statement = $pdo->prepare('DELETE FROM survey_responses WHERE id = :id');
    $statement->execute([':id' => (int) ($_POST['id'] ?? 0)]);

    header('Location: index.php?deleted=' . $statement->rowCount());
    exit;
}

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
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">
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
            <header class="mb-4">
                <p class="admin-eyebrow mb-1">Survey responses</p>
                <h1 class="h3 mb-1">Response not found</h1>
                <p class="text-muted mb-0">That response does not exist.</p>
            </header>

            <a class="btn btn-outline-secondary" href="index.php">Back to all responses</a>
        <?php else: ?>
            <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <p class="admin-eyebrow mb-1">Survey response</p>
                    <h1 class="h3 mb-1">Response #<?= (int) $response['id'] ?></h1>
                    <p class="text-muted mb-0">
                        Submitted <?= e(date('j M Y, g:ia', strtotime((string) $response['submitted_at']))) ?>
                    </p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a class="btn btn-outline-secondary" href="index.php">Back to all responses</a>

                    <form method="post" action="view.php" data-confirm="Delete response #<?= (int) $response['id'] ?>? This cannot be undone.">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $response['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete response</button>
                    </form>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
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
                                    <span class="text-muted">No answer</span>
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
            </div>
        <?php endif; ?>
    </main>

    <script src="../assets/survey-admin.js"></script>
</body>
</html>
