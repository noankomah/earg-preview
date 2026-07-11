<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/opportunity_helpers.php';

require_admin();

$pdo = db();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM opportunities
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id,
]);

$opportunity = $stmt->fetch();

if (!$opportunity) {
    http_response_code(404);
    echo 'Opportunity not found.';
    exit;
}

$errors = [];

$data = [
    'title' => (string) $opportunity['title'],
    'category' => (string) $opportunity['category'],
    'summary' => (string) $opportunity['summary'],
    'full_description' => (string) $opportunity['full_description'],
    'deadline' => (string) ($opportunity['deadline'] ?? ''),
    'status' => (string) $opportunity['status'],
    'country' => (string) ($opportunity['country'] ?? ''),
    'host_organization' => (string) ($opportunity['host_organization'] ?? ''),
    'application_link' => (string) ($opportunity['application_link'] ?? ''),
    'is_published' => (string) $opportunity['is_published'],
    'is_featured' => (string) $opportunity['is_featured'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['title'] = trim((string) ($_POST['title'] ?? ''));
    $data['category'] = trim((string) ($_POST['category'] ?? ''));
    $data['summary'] = trim((string) ($_POST['summary'] ?? ''));
    $data['full_description'] = trim((string) ($_POST['full_description'] ?? ''));
    $data['deadline'] = trim((string) ($_POST['deadline'] ?? ''));
    $data['status'] = trim((string) ($_POST['status'] ?? ''));
    $data['country'] = trim((string) ($_POST['country'] ?? ''));
    $data['host_organization'] = trim((string) ($_POST['host_organization'] ?? ''));
    $data['application_link'] = trim((string) ($_POST['application_link'] ?? ''));
    $data['is_published'] = isset($_POST['is_published']) ? '1' : '0';
    $data['is_featured'] = isset($_POST['is_featured']) ? '1' : '0';

    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (!in_array($data['category'], opportunity_categories(), true)) {
        $errors[] = 'Please select a valid category.';
    }

    if ($data['summary'] === '') {
        $errors[] = 'Summary is required.';
    }

    if ($data['full_description'] === '') {
        $errors[] = 'Full description is required.';
    }

    if (!in_array($data['status'], opportunity_statuses(), true)) {
        $errors[] = 'Please select a valid status.';
    }

    if ($data['application_link'] !== '' && !filter_var($data['application_link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Application link must be a valid URL.';
    }

    $deadline = clean_date_or_null($data['deadline']);

    if ($data['deadline'] !== '' && $deadline === null) {
        $errors[] = 'Deadline must be a valid date.';
    }

    if (!$errors) {
        $slug = unique_opportunity_slug($pdo, $data['title'], $id);

        $update = $pdo->prepare("
            UPDATE opportunities
            SET
                title = :title,
                slug = :slug,
                category = :category,
                summary = :summary,
                full_description = :full_description,
                deadline = :deadline,
                status = :status,
                country = :country,
                host_organization = :host_organization,
                application_link = :application_link,
                is_published = :is_published,
                is_featured = :is_featured
            WHERE id = :id
        ");

        $update->execute([
            ':title' => $data['title'],
            ':slug' => $slug,
            ':category' => $data['category'],
            ':summary' => $data['summary'],
            ':full_description' => $data['full_description'],
            ':deadline' => $deadline,
            ':status' => $data['status'],
            ':country' => $data['country'] !== '' ? $data['country'] : null,
            ':host_organization' => $data['host_organization'] !== '' ? $data['host_organization'] : null,
            ':application_link' => $data['application_link'] !== '' ? $data['application_link'] : null,
            ':is_published' => (int) $data['is_published'],
            ':is_featured' => (int) $data['is_featured'],
            ':id' => $id,
        ]);

        header('Location: index.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Opportunity | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="admin-body">

    <header class="admin-topbar">
        <div>
            <strong>EA Research Group</strong>
            <span>Edit Opportunity</span>
        </div>

        <nav class="admin-topnav">
            <a href="index.php">Back to opportunities</a>
            <a href="../logout.php">Log out</a>
        </nav>
    </header>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">Edit opportunity</p>
            <h1><?= e($data['title']) ?></h1>

            <?php if ($errors): ?>
                <div class="admin-alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="admin-form admin-wide-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label>
                    Title
                    <input type="text" name="title" value="<?= e($data['title']) ?>" required>
                </label>

                <label>
                    Category
                    <select name="category" required>
                        <?php foreach (opportunity_categories() as $category): ?>
                            <option value="<?= e($category) ?>" <?= $data['category'] === $category ? 'selected' : '' ?>>
                                <?= e($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Short summary
                    <textarea name="summary" rows="4" required><?= e($data['summary']) ?></textarea>
                </label>

                <label>
                    Full description
                    <textarea name="full_description" rows="10" required><?= e($data['full_description']) ?></textarea>
                </label>

                <div class="admin-two-cols">
                    <label>
                        Deadline
                        <input type="date" name="deadline" value="<?= e($data['deadline']) ?>">
                    </label>

                    <label>
                        Status
                        <select name="status" required>
                            <?php foreach (opportunity_statuses() as $status): ?>
                                <option value="<?= e($status) ?>" <?= $data['status'] === $status ? 'selected' : '' ?>>
                                    <?= e($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="admin-two-cols">
                    <label>
                        Country
                        <input type="text" name="country" value="<?= e($data['country']) ?>">
                    </label>

                    <label>
                        Host organization
                        <input type="text" name="host_organization" value="<?= e($data['host_organization']) ?>">
                    </label>
                </div>

                <label>
                    Application link
                    <input type="url" name="application_link" value="<?= e($data['application_link']) ?>">
                </label>

                <div class="admin-checks">
                    <label>
                        <input type="checkbox" name="is_published" value="1" <?= $data['is_published'] === '1' ? 'checked' : '' ?>>
                        Publish on website
                    </label>

                    <label>
                        <input type="checkbox" name="is_featured" value="1" <?= $data['is_featured'] === '1' ? 'checked' : '' ?>>
                        Mark as featured
                    </label>
                </div>

                <button type="submit">Update opportunity</button>
            </form>
        </section>
    </main>

</body>
</html>
