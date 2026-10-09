<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/admin/includes/content_helpers.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$opportunity = null;
$errorMessage = '';

if ($slug === '') {
    http_response_code(404);
    $errorMessage = 'The requested opportunity could not be found.';
} else {
    try {
        $pdo = db();

        $statement = $pdo->prepare(
            "
            SELECT
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
                is_featured,
                created_at,
                updated_at
            FROM opportunities
            WHERE slug = :slug
              AND is_published = 1
            LIMIT 1
            "
        );

        $statement->execute([
            'slug' => $slug,
        ]);

        $opportunity = $statement->fetch();

        if (!$opportunity) {
            http_response_code(404);
            $errorMessage = 'The requested opportunity could not be found.';
        }
    } catch (Throwable $exception) {
        error_log('Public opportunity page error: ' . $exception->getMessage());

        http_response_code(500);
        $errorMessage = 'This opportunity cannot be displayed at the moment.';
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_public_date(?string $date): string
{
    if ($date === null || $date === '') {
        return 'Not specified';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return $date;
    }

    return date('j F Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $opportunity
            ? e((string) $opportunity['title']) . ' | EA Research Group'
            : 'Opportunity Not Found | EA Research Group'
        ?>
    </title>

    <meta
        name="description"
        content="<?= $opportunity
            ? e((string) $opportunity['summary'])
            : 'EA Research Group opportunity information.'
        ?>"
    >

    <link rel="stylesheet" href="assets/css/about.css?v=2">
    <link rel="stylesheet" href="assets/css/publications.css?v=3">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="opportunity-detail-page">
        <section class="about-route publication-route">
            <div class="container">

                <a
                    href="publications.php#scholarship-opportunities"
                    class="opportunity-back-link"
                >
                    ← Back to opportunities
                </a>

                <?php if ($opportunity): ?>

                    <article class="opportunity-detail-card">

                        <div class="opportunity-detail-heading">
                            <p class="opportunity-type">
                                <?= e((string) $opportunity['category']) ?>
                            </p>

                            <?php if ((int) $opportunity['is_featured'] === 1): ?>
                                <span class="opportunity-featured-badge">
                                    Featured
                                </span>
                            <?php endif; ?>
                        </div>

                        <h1><?= e((string) $opportunity['title']) ?></h1>

                        <p class="opportunity-detail-summary">
                            <?= e((string) $opportunity['summary']) ?>
                        </p>

                        <div class="opportunity-detail-meta">
                            <div>
                                <strong>Status</strong>
                                <span><?= e((string) $opportunity['status']) ?></span>
                            </div>

                            <div>
                                <strong>Deadline</strong>
                                <span>
                                    <?= e(format_public_date($opportunity['deadline'])) ?>
                                </span>
                            </div>

                            <?php if (!empty($opportunity['country'])): ?>
                                <div>
                                    <strong>Country</strong>
                                    <span><?= e((string) $opportunity['country']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($opportunity['host_organization'])): ?>
                                <div>
                                    <strong>Host organization</strong>
                                    <span>
                                        <?= e((string) $opportunity['host_organization']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="opportunity-detail-body">
                            <?= sanitize_rich_text((string) $opportunity['full_description']) ?>
                        </div>

                        <?php if (!empty($opportunity['application_link'])): ?>
                            <a
                                href="<?= e((string) $opportunity['application_link']) ?>"
                                class="opportunity-apply-button"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Visit application page
                            </a>
                        <?php endif; ?>

                    </article>

                <?php else: ?>

                    <section class="opportunity-state opportunity-error-state">
                        <h1>Opportunity not found</h1>
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
