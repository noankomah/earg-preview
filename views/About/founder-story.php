<?php
/**
 * Founder's Story - standalone About page.
 *
 * Moved from the old /founder-story.php into views/About/. The content is
 * still edited in the admin area (admin/founder_story.php) and stored in
 * the founder_story table. Paths to the shared admin includes and uploads
 * are adjusted for this sub-folder, and the uploaded photo path is passed
 * through url() so it loads from here.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/site.php';
require_once __DIR__ . '/../../admin/includes/db.php';
require_once __DIR__ . '/../../admin/includes/content_helpers.php';

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch the published founder story (single record).
$story = null;
$sections = [];
$errorMessage = '';

try {
    $pdo = db();

    $statement = $pdo->query("
        SELECT
            founder_name,
            role_title,
            photo_url,
            sections,
            created_at,
            updated_at
        FROM founder_story
        WHERE is_published = 1
        LIMIT 1
    ");

    $story = $statement->fetch();

    if (!$story) {
        http_response_code(404);
        $errorMessage = 'The founder story is not available yet.';
    } else {
        // Sections are stored as JSON: [{ "heading": "...", "body": "..." }].
        // Older single-block rich text content is shown as one section.
        $rawSections = $story['sections'];

        if ($rawSections !== null && trim((string) $rawSections) !== '') {
            $decoded = json_decode((string) $rawSections, true);

            if (is_array($decoded)) {
                foreach ($decoded as $section) {
                    $sections[] = [
                        'heading' => (string) ($section['heading'] ?? ''),
                        'body' => (string) ($section['body'] ?? ''),
                    ];
                }
            } else {
                $sections[] = [
                    'heading' => '',
                    'body' => (string) $rawSections,
                ];
            }
        }
    }
} catch (Throwable $exception) {
    error_log('Public founder story page error: ' . $exception->getMessage());

    http_response_code(500);
    $errorMessage = 'This page cannot be displayed at the moment.';
}

// Uploaded photos are stored relative to the site root, so make sure the
// path still resolves when this page lives in views/About/.
$photoSrc = '';
if ($story && !empty($story['photo_url'])) {
    $photoUrl = (string) $story['photo_url'];
    $photoSrc = preg_match('#^https?://#i', $photoUrl) ? $photoUrl : url($photoUrl);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $story
            ? 'Founder\'s Story | EA Research Group'
            : 'Founder Story Not Found | EA Research Group'
        ?>
    </title>

    <meta
        name="description"
        content="The story behind <?= $story ? e((string) $story['founder_name']) : 'EA Research Group' ?>, founder of EA Research Group."
    >

    <link rel="stylesheet" href="<?= url('assets/css/about.css?v=4') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/publications.css?v=7') ?>">
    <script defer src="<?= url('assets/vendor/alpinejs/alpine.min.js') ?>"></script>
</head>

<body>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="about-route publication-route founder-story-page">
        <section class="about-route publication-route">
            <div class="container">

                <?php if ($story): ?>

                    <article class="founder-story-card">

                        <div class="founder-story-heading">
                            <p class="publication-type">Founder's Story</p>
                        </div>

                        <div class="founder-story-grid">
                            <?php if ($photoSrc !== ''): ?>
                                <div class="founder-story-photo">
                                    <img
                                        src="<?= e($photoSrc) ?>"
                                        alt="Photo of <?= e((string) $story['founder_name']) ?>"
                                    >
                                </div>
                            <?php endif; ?>

                            <div class="founder-story-main">
                                <h1><?= e((string) $story['founder_name']) ?></h1>

                                <?php if (!empty($story['role_title'])): ?>
                                    <p class="founder-story-role">
                                        <?= e((string) $story['role_title']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($sections): ?>
                            <div class="founder-story-sections">
                                <?php foreach ($sections as $section): ?>
                                    <?php if ($section['heading'] === '' && $section['body'] === ''): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>

                                    <section class="founder-story-section">
                                        <?php if ($section['heading'] !== ''): ?>
                                            <h2><?= e($section['heading']) ?></h2>
                                        <?php endif; ?>

                                        <div class="founder-story-body">
                                            <?= sanitize_rich_text($section['body']) ?>
                                        </div>
                                    </section>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </article>

                <?php else: ?>

                    <section class="opportunity-state opportunity-error-state">
                        <h1>Founder story not available</h1>
                        <p><?= e($errorMessage) ?></p>
                    </section>

                <?php endif; ?>

            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="<?= url('assets/js/about-pages.js') ?>"></script>
</body>
</html>
