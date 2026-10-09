<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/opportunity_helpers.php';
require_once __DIR__ . '/../includes/content_helpers.php';

require_admin();

$pdo = db();
$errors = [];

$data = [
    'title' => '',
    'category' => 'Scholarship',
    'summary' => '',
    'full_description' => '',
    'deadline' => '',
    'status' => 'Open',
    'country' => '',
    'host_organization' => '',
    'application_link' => '',
    'is_published' => '1',
    'is_featured' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $data['title'] = trim((string) ($_POST['title'] ?? ''));
    $data['category'] = trim((string) ($_POST['category'] ?? ''));
    $data['summary'] = trim((string) ($_POST['summary'] ?? ''));
    $data['full_description'] = sanitize_rich_text((string) ($_POST['full_description'] ?? ''));
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
        $slug = unique_opportunity_slug($pdo, $data['title']);

        $stmt = $pdo->prepare("
            INSERT INTO opportunities (
                title,
                slug,
                category,
                summary,
                full_description,
                deadline,
                status,
                country,
                host_organization,
                application_link,
                is_published,
                is_featured
            )
            VALUES (
                :title,
                :slug,
                :category,
                :summary,
                :full_description,
                :deadline,
                :status,
                :country,
                :host_organization,
                :application_link,
                :is_published,
                :is_featured
            )
        ");

        $stmt->execute([
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
        ]);

        header('Location: index.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Opportunity | EA Research Group</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link
        href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
        rel="stylesheet"
    >
</head>
<body class="admin-body">

    <?php
    // Details for the shared admin navigation (admin/includes/admin_navbar.php).
    $adminPage = 'Add Opportunity';
    $adminLinks = array(
        array('Back to opportunities', 'index.php'),
        array('Log out', '../logout.php'),
    );
    ?>
    <?php include __DIR__ . '/../includes/admin_navbar.php'; ?>

    <main class="admin-dashboard">
        <section class="admin-form-card">
            <p class="admin-eyebrow">New opportunity</p>
            <h1>Add opportunity</h1>

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

                <label for="full_description_editor">
                    Full description
                </label>

                <input
                    type="hidden"
                    name="full_description"
                    id="full_description"
                    value="<?= e($data['full_description']) ?>"
                >

                <div
                    id="full_description_editor"
                    class="admin-rich-text-editor"
                    data-rich-text-editor
                    data-target="full_description"
                    data-placeholder="Enter the full opportunity description..."
                ></div>

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

                <button type="submit">Save opportunity</button>
            </form>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="../assets/rich-text.js"></script>                                
</body>
</html>
