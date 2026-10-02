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
    <header class="site-header" x-data="{ open: false }">
        <div class="container header-inner">

            <a href="index.html" class="brand" aria-label="EA Research Group home">
                <img
                    src="assets/images/earg-logo.png"
                    alt="EA Research Group Logo"
                    class="brand-logo"
                >

                <span class="brand-text">
                    <strong>EA Research Group</strong>
                    <small>Research • Mentorship • Innovation • Impact</small>
                </span>
            </a>

            <button
                class="menu-toggle"
                type="button"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-label="Toggle navigation"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav
                class="main-nav"
                :class="{ 'is-open': open }"
                aria-label="Primary navigation"
            >
                <a href="index.html">Home</a>

                <div class="nav-dropdown">
                    <button type="button" class="nav-parent">About Us</button>

                    <div class="dropdown-menu">
                        <a href="about.html#who-we-are">Who We Are</a>
                        <a href="about.html#governing-board">Governing Board</a>
                        <a href="about.html#our-team">Our Team</a>
                        <a href="founder-story.php">Founder's Story</a>
                        <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Work With Us</a>
                        <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Our Partners</a>
                        <a href="about.html#contact-us">Contact Us</a>
                    </div>
                </div>

                <div class="nav-dropdown">
                    <button type="button" class="nav-parent">Programs</button>

                    <div class="dropdown-menu">
                        <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Mentorship and Fellowship</a>
                        <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Research Capacity</a>
                        <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Community Research</a>
                        <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Consultancy and Partnerships</a>
                    </div>
                </div>

                <div class="nav-dropdown">
                    <button type="button" class="nav-parent">Publications</button>

                    <div class="dropdown-menu">
                        <a href="publications.html#blogs">Blogs</a>
                        <a href="publications.html#newsletters">Newsletters</a>
                        <a href="publications.html#reports">Reports</a>
                        <a href="publications.html#scholarship-opportunities">
                            Scholarship & Opportunities
                        </a>
                    </div>
                </div>

                <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Events</a>
            </nav>

        </div>
    </header>

    <main class="opportunity-detail-page">
        <section class="about-route publication-route">
            <div class="container">

                <a
                    href="publications.html#scholarship-opportunities"
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

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <img
                    src="assets/images/earg-logo-transparent.png"
                    alt="EA Research Group Logo"
                    class="footer-logo"
                >

                <p>
                    Effective Altruist Research Group is a non-profit research and
                    academic development organization advancing research,
                    mentorship, innovation and impact.
                </p>
            </div>

            <div>
                <h4>Quick Links</h4>
                <a href="about.html#who-we-are">Who We Are</a>
                <a href="about.html#governing-board">Governing Board</a>
                <a href="about.html#our-team">Our Team</a>
                <a href="about.html#contact-us">Contact Us</a>
            </div>

            <div>
                <h4>Find Us</h4>
                <p>Tamale, Northern Region, Ghana</p>
                <p>
                    <a href="mailto:earesearchgrp24@gmail.com">
                        earesearchgrp24@gmail.com
                    </a>
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                © <span id="year"></span> EA Research Group.
                All rights reserved.
            </p>
        </div>
    </footer>

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
