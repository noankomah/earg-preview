<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/admin/includes/content_helpers.php';
require_once __DIR__ . '/admin/includes/publication_helpers.php';

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
                gallery_images,
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

// Images shown on the page. New publications store several uploaded images in
// gallery_images (JSON), but older records may only have a single cover image.
$gallery = [];
$lightboxImages = [];

if ($publication) {
    $gallery = decode_publication_gallery(
        $publication['gallery_images'] ?? null
    );

    if ($gallery !== []) {
        $lightboxImages = $gallery;
    } elseif (!empty($publication['cover_image'])) {
        $lightboxImages = [(string) $publication['cover_image']];
    }
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
    <link rel="stylesheet" href="assets/css/publications.css?v=6">
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

                    <div class="publication-detail-layout">

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

                    <?php if ($lightboxImages): ?>
                        <aside
                            class="publication-detail-images publication-gallery-root"
                            id="publication-gallery-root"
                            aria-label="Publication images"
                            data-images="<?= e(json_encode(
                                $lightboxImages,
                                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                            )) ?>"
                        >
                            <p class="publication-images-label">
                                Images
                            </p>

                            <?php if ($gallery): ?>
                                <div class="publication-gallery">
                                    <?php foreach ($gallery as $index => $imagePath): ?>
                                        <button
                                            type="button"
                                            class="publication-gallery-thumb publication-gallery-zoom"
                                            data-index="<?= (int) $index ?>"
                                            aria-label="Zoom image <?= $index + 1 ?>"
                                        >
                                            <img
                                                src="<?= e($imagePath) ?>"
                                                alt="<?= e((string) $publication['title']) ?>"
                                                loading="lazy"
                                            >
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (!empty($publication['cover_image'])): ?>
                                <button
                                    type="button"
                                    class="publication-cover-zoom publication-gallery-zoom"
                                    data-index="0"
                                    aria-label="Zoom image"
                                >
                                    <img
                                        src="<?= e((string) $publication['cover_image']) ?>"
                                        alt="<?= e((string) $publication['title']) ?>"
                                        class="publication-cover-image"
                                    >
                                </button>
                            <?php endif; ?>
                        </aside>
                    <?php endif; ?>

                    </div>

                <?php else: ?>

                    <section class="opportunity-state opportunity-error-state">
                        <h1>Publication not found</h1>
                        <p><?= e($errorMessage) ?></p>
                    </section>

                <?php endif; ?>

            </div>
        </section>
    </main>

    <?php if ($lightboxImages): ?>
        <div
            class="publication-lightbox"
            id="publication-lightbox"
            role="dialog"
            aria-modal="true"
            aria-label="Image viewer"
            hidden
        >
            <button
                type="button"
                class="publication-lightbox-close"
                id="publication-lightbox-close"
                aria-label="Close image viewer"
            >&times;</button>

            <?php if (count($lightboxImages) > 1): ?>
                <button
                    type="button"
                    class="publication-lightbox-nav publication-lightbox-prev"
                    id="publication-lightbox-prev"
                    aria-label="Previous image"
                >&lsaquo;</button>

                <button
                    type="button"
                    class="publication-lightbox-nav publication-lightbox-next"
                    id="publication-lightbox-next"
                    aria-label="Next image"
                >&rsaquo;</button>
            <?php endif; ?>

            <figure class="publication-lightbox-figure">
                <img id="publication-lightbox-img" src="" alt="">
            </figure>
        </div>
    <?php endif; ?>

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

            const galleryRoot = document.getElementById("publication-gallery-root");

            if (galleryRoot) {
                let galleryImages = [];

                try {
                    galleryImages = JSON.parse(galleryRoot.dataset.images) || [];
                } catch (e) {
                    galleryImages = [];
                }

                const lightbox = document.getElementById("publication-lightbox");
                const lightboxImg = document.getElementById("publication-lightbox-img");
                const closeBtn = document.getElementById("publication-lightbox-close");
                const prevBtn = document.getElementById("publication-lightbox-prev");
                const nextBtn = document.getElementById("publication-lightbox-next");
                let activeIndex = 0;

                function openLightbox(index) {
                    if (!lightbox || !galleryImages.length) return;

                    activeIndex = (index + galleryImages.length) % galleryImages.length;

                    lightboxImg.src = galleryImages[activeIndex];
                    lightboxImg.alt = "Viewing image " + (activeIndex + 1) + " of " + galleryImages.length;
                    lightbox.classList.add("is-open");
                    lightbox.removeAttribute("hidden");
                    document.body.classList.add("publication-no-scroll");
                }

                function closeLightbox() {
                    if (!lightbox) return;

                    lightbox.classList.remove("is-open");
                    lightbox.setAttribute("hidden", "");
                    document.body.classList.remove("publication-no-scroll");
                }

                if (closeBtn) {
                    closeBtn.addEventListener("click", closeLightbox);
                }

                // One delegated listener handles every zoomable image, so it
                // keeps working even if images are added or replaced later.
                galleryRoot.addEventListener("click", function (event) {
                    var button = event.target.closest(".publication-gallery-zoom");

                    if (!button) return;

                    event.preventDefault();
                    event.stopPropagation();

                    openLightbox(Number(button.getAttribute("data-index") || 0));
                });

                if (prevBtn) {
                    prevBtn.addEventListener("click", function () {
                        openLightbox(activeIndex - 1);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener("click", function () {
                        openLightbox(activeIndex + 1);
                    });
                }

                // Clicking the dark area outside the image closes the viewer.
                if (lightbox) {
                    lightbox.addEventListener("click", function (event) {
                        if (event.target === lightbox || event.target === lightboxImg) {
                            closeLightbox();
                        }
                    });
                }

                document.addEventListener("keydown", function (event) {
                    if (!lightbox || !lightbox.classList.contains("is-open")) return;

                    if (event.key === "Escape") {
                        closeLightbox();
                    } else if (event.key === "ArrowLeft" && prevBtn) {
                        openLightbox(activeIndex - 1);
                    } else if (event.key === "ArrowRight" && nextBtn) {
                        openLightbox(activeIndex + 1);
                    }
                });
            }
        });
    </script>
</body>
</html>
