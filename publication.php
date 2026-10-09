<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/admin/includes/content_helpers.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$publication = null;
$errorMessage = '';

if ($slug === '') {
    http_response_code(404);
    $errorMessage = 'The requested publication could not be found.';
} else {
    try {
        $pdo = db();

        $statement = $pdo->prepare(
            "
            SELECT
                title,
                slug,
                publication_type,
                summary,
                full_content,
                author,
                publication_date,
                cover_image,
                document_link,
                is_featured,
                created_at,
                updated_at
            FROM publications
            WHERE slug = :slug
              AND is_published = 1
            LIMIT 1
            "
        );

        $statement->execute([
            ':slug' => $slug,
        ]);

        $publication = $statement->fetch();

        if (!$publication) {
            http_response_code(404);
            $errorMessage = 'The requested publication could not be found.';
        }
    } catch (Throwable $exception) {
        error_log('Public publication page error: ' . $exception->getMessage());

        http_response_code(500);
        $errorMessage = 'This publication cannot be displayed at the moment.';
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_publication_date(?string $date): string
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
        <?= $publication
            ? e((string) $publication['title']) . ' | EA Research Group'
            : 'Publication Not Found | EA Research Group'
        ?>
    </title>

    <meta
        name="description"
        content="<?= $publication
            ? e((string) $publication['summary'])
            : 'EA Research Group publication.'
        ?>"
    >

    <link rel="stylesheet" href="assets/css/about.css?v=2">
    <link rel="stylesheet" href="assets/css/publications.css?v=4">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="publication-detail-page">
        <section class="publication-route">
            <div class="container">

                <a
                    href="publications.php"
                    class="publication-back-link"
                    id="publication-back-link"
                >
                    ← Back to publications
                </a>

                <?php if ($publication): ?>

                    <article class="publication-detail-card">

                        <div class="publication-detail-heading">
                            <p class="publication-type">
                                <?= e((string) $publication['publication_type']) ?>
                            </p>

                            <?php if ((int) $publication['is_featured'] === 1): ?>
                                <span class="publication-featured-badge">
                                    Featured
                                </span>
                            <?php endif; ?>
                        </div>

                        <h1><?= e((string) $publication['title']) ?></h1>

                        <p class="publication-detail-summary">
                            <?= e((string) $publication['summary']) ?>
                        </p>

                        <div class="publication-detail-meta">
                            <div>
                                <strong>Publication type</strong>
                                <span>
                                    <?= e((string) $publication['publication_type']) ?>
                                </span>
                            </div>

                            <div>
                                <strong>Publication date</strong>
                                <span>
                                    <?= e(format_publication_date(
                                        $publication['publication_date']
                                    )) ?>
                                </span>
                            </div>

                            <?php if (!empty($publication['author'])): ?>
                                <div>
                                    <strong>Author</strong>
                                    <span><?= e((string) $publication['author']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($publication['cover_image'])): ?>
                            <img
                                src="<?= e((string) $publication['cover_image']) ?>"
                                alt="<?= e((string) $publication['title']) ?>"
                                class="publication-cover-image"
                            >
                        <?php endif; ?>

                        <div class="publication-detail-body">
                            <?= sanitize_rich_text(
                                (string) $publication['full_content']
                            ) ?>
                        </div>

                        <?php if (!empty($publication['document_link'])): ?>
                            <a
                                href="<?= e((string) $publication['document_link']) ?>"
                                class="publication-document-button"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Open document
                            </a>
                        <?php endif; ?>

                    </article>

                <?php else: ?>

                    <section class="opportunity-state opportunity-error-state">
                        <h1>Publication not found</h1>
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

            const publicationType =
                <?= json_encode(
                    $publication['publication_type'] ?? '',
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ) ?>;

            const backLink = document.getElementById("publication-back-link");

            if (backLink) {
                const routeMap = {
                    Blog: "blogs",
                    Newsletter: "newsletters",
                    Report: "reports"
                };

                const route = routeMap[publicationType] || "blogs";

                backLink.href = `publications.php#${route}`;
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
