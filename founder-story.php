<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/admin/includes/content_helpers.php';

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

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

    <link rel="stylesheet" href="assets/css/about.css?v=2">
    <link rel="stylesheet" href="assets/css/publications.css?v=5">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="about-route publication-route founder-story-page">
        <section class="about-route publication-route">
            <div class="container">

                <?php if ($story): ?>

                    <article class="founder-story-card">

                        <div class="founder-story-heading">
                            <p class="publication-type">Founder's Story</p>
                        </div>

                        <div class="founder-story-grid">
                            <?php if (!empty($story['photo_url'])): ?>
                                <div class="founder-story-photo">
                                    <img
                                        src="<?= e((string) $story['photo_url']) ?>"
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

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const year = document.getElementById("year");

            if (year) {
                year.textContent = new Date().getFullYear();
            }

            const dropdownButtons = document.querySelectorAll(".nav-parent");

            dropdownButtons.forEach(function (button) {
                button.addEventListener("click", function () {
                    if (window.innerWidth > 1024) return;

                    const dropdown = button.closest(".nav-dropdown");

                    if (dropdown) {
                        dropdown.classList.toggle("is-open");
                    }
                });
            });
        });
    </script>
</body>
</html>