<?php

declare(strict_types=1);

/**
 * Admin > Survey responses.
 *
 * Shows the number of responses, charts for each question (drawn by
 * admin/assets/survey-charts.js from stats.php), a list of individual
 * responses to open, the written answers, and a CSV export button.
 *
 * Styling uses Bootstrap (assets/vendor/bootstrap) on top of the shared
 * admin chrome (admin.css). The table behaviour (search, sorting, pagination,
 * bulk selection) lives in admin/assets/survey-admin.js.
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

/*
 * Delete actions arrive by POST (with a CSRF token). After deleting we
 * redirect back to the same filtered view (Post/Redirect/Get), so a browser
 * refresh never repeats the deletion.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectFrom = survey_valid_date($_POST['from'] ?? '');
    $redirectTo = survey_valid_date($_POST['to'] ?? '');
    $redirectQuery = array_filter(['from' => $redirectFrom, 'to' => $redirectTo]);

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        header('Location: index.php?' . http_build_query(array_merge($redirectQuery, ['error' => 'csrf'])));
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');
    $deleted = 0;

    if ($action === 'delete_one') {
        $statement = $pdo->prepare('DELETE FROM survey_responses WHERE id = :id');
        $statement->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        $deleted = $statement->rowCount();
    } elseif ($action === 'delete_selected') {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', (array) ($_POST['ids'] ?? [])),
            static function (int $id): bool {
                return $id > 0;
            }
        )));

        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $statement = $pdo->prepare('DELETE FROM survey_responses WHERE id IN (' . $placeholders . ')');
            $statement->execute($ids);
            $deleted = $statement->rowCount();
        }
    } elseif ($action === 'delete_all') {
        $statement = $pdo->query('DELETE FROM survey_responses');
        $deleted = $statement->rowCount();
    } elseif ($action === 'delete_range' && ($redirectFrom !== '' || $redirectTo !== '')) {
        $rangeWhere = '1=1';
        $rangeParams = [];

        if ($redirectFrom !== '') {
            $rangeWhere .= ' AND submitted_at >= :from';
            $rangeParams[':from'] = $redirectFrom . ' 00:00:00';
        }

        if ($redirectTo !== '') {
            $rangeWhere .= ' AND submitted_at <= :to';
            $rangeParams[':to'] = $redirectTo . ' 23:59:59';
        }

        $statement = $pdo->prepare('DELETE FROM survey_responses WHERE ' . $rangeWhere);
        $statement->execute($rangeParams);
        $deleted = $statement->rowCount();
    }

    header('Location: index.php?' . http_build_query(array_merge($redirectQuery, ['deleted' => $deleted])));
    exit;
}

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
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/vendor/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../../assets/vendor/datatables/dataTables.bootstrap5.min.css">
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

        <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="admin-eyebrow mb-1">Mentorship &amp; career readiness survey</p>
                <h1 class="h3 mb-1">Survey responses</h1>
                <p class="text-muted mb-0">
                    <?= $total ?> response<?= $total === 1 ? '' : 's' ?>
                    <?php if ($from !== '' || $to !== ''): ?>
                        (<?= e($from !== '' ? $from : 'the beginning') ?> to <?= e($to !== '' ? $to : 'today') ?>)
                    <?php endif; ?>
                </p>
            </div>

            <a class="btn btn-success" href="export.php?<?= e($exportQuery) ?>">
                Download CSV
            </a>
        </header>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success" role="status">
                <?= (int) $_GET['deleted'] ?> response<?= (int) $_GET['deleted'] === 1 ? '' : 's' ?> deleted.
            </div>
        <?php endif; ?>

        <?php if (($_GET['error'] ?? '') === 'csrf'): ?>
            <div class="alert alert-danger" role="alert">
                Security check failed. Please try again.
            </div>
        <?php endif; ?>

        <form class="row g-2 align-items-end mb-4" method="get" action="index.php">
            <div class="col-auto">
                <label class="form-label mb-1" for="filterFrom">From</label>
                <input type="date" class="form-control" id="filterFrom" name="from" value="<?= e($from) ?>">
            </div>
            <div class="col-auto">
                <label class="form-label mb-1" for="filterTo">To</label>
                <input type="date" class="form-control" id="filterTo" name="to" value="<?= e($to) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($from !== '' || $to !== ''): ?>
                    <a class="btn btn-outline-secondary" href="index.php">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="display-6 fw-bold text-success"><?= $total ?></div>
                        <h2 class="h6 text-uppercase text-muted mt-2 mb-1">Total responses</h2>
                        <p class="card-text text-muted mb-0">
                            <?= count($responses) ?> listed below<?= $total > count($responses) ? ' (showing the most recent 500)' : '' ?>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts are drawn here by admin/assets/survey-charts.js. -->
        <section id="surveyCharts" class="row g-3 mb-4" data-from="<?= e($from) ?>" data-to="<?= e($to) ?>">
            <div class="col-12"><p class="text-muted mb-0">Loading charts...</p></div>
        </section>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Individual responses</h2>

                <?php if (!$responses): ?>
                    <p class="text-muted fw-semibold mb-0">No responses yet.</p>
                <?php else: ?>
                    <!--
                        Bulk selection is tracked in JS and written into this form as
                        hidden "ids[]" inputs. This is because DataTables removes
                        off-page rows from the DOM, so their checkboxes would not be
                        submitted with the form.
                    -->
                    <form id="bulkForm" method="post" action="index.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="from" value="<?= e($from) ?>">
                        <input type="hidden" name="to" value="<?= e($to) ?>">
                        <div id="bulkIds"></div>
                    </form>

                    <table class="table table-striped align-middle" id="responsesTable">
                        <thead>
                            <tr>
                                <th class="survey-check-col">
                                    <input type="checkbox" id="selectAll" aria-label="Select all responses">
                                </th>
                                <th>#</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responses as $response): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" data-row-check data-id="<?= (int) $response['id'] ?>">
                                    </td>
                                    <td><?= (int) $response['id'] ?></td>
                                    <td><?= e(date('j M Y, g:ia', strtotime((string) $response['submitted_at']))) ?></td>
                                    <td class="text-nowrap">
                                        <a class="btn btn-sm btn-outline-secondary" href="view.php?id=<?= (int) $response['id'] ?>">View</a>

                                        <form method="post" action="index.php" class="d-inline" data-confirm="Delete response #<?= (int) $response['id'] ?>? This cannot be undone.">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete_one">
                                            <input type="hidden" name="id" value="<?= (int) $response['id'] ?>">
                                            <input type="hidden" name="from" value="<?= e($from) ?>">
                                            <input type="hidden" name="to" value="<?= e($to) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" form="bulkForm" name="action" value="delete_selected"
                                class="btn btn-outline-danger"
                                data-confirm="Delete the selected responses? This cannot be undone.">
                            Delete selected
                        </button>
                        <button type="submit" form="bulkForm" name="action" value="delete_all"
                                class="btn btn-danger"
                                data-confirm="Delete ALL survey responses? This cannot be undone.">
                            Delete all
                        </button>
                        <?php if ($from !== '' || $to !== ''): ?>
                            <button type="submit" form="bulkForm" name="action" value="delete_range"
                                    class="btn btn-outline-danger"
                                    data-confirm="Delete every response in the selected date range? This cannot be undone.">
                                Clear date range
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Written answers</h2>

                <?php
                $writtenSections = $writtenAnswers;
                if ($otherAnswers) {
                    $writtenSections['q11_other'] = $otherAnswers;
                }
                ?>

                <?php if (empty($writtenSections)): ?>
                    <p class="text-muted fw-semibold mb-0">Nothing written yet.</p>
                <?php else: ?>
                    <?php foreach ($writtenSections as $key => $answers): ?>
                        <div class="survey-written-block">
                            <h3 class="h6">
                                <?php if ($key === 'q11_other'): ?>
                                    11. Other barrier (please specify)
                                <?php else: ?>
                                    <?= (int) $writtenKeys[$key]['number'] ?>. <?= e($writtenKeys[$key]['title']) ?>
                                <?php endif; ?>
                            </h3>

                            <?php if (!$answers): ?>
                                <p class="text-muted mb-0">No answers yet.</p>
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
            </div>
        </div>
    </main>

    <script src="../../assets/vendor/chartjs/chart.umd.min.js"></script>
    <script src="../assets/survey-charts.js"></script>
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/survey-admin.js"></script>
</body>
</html>
