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
    <header class="site-header" x-data="{ open: false }">
        <div class="container header-inner">

            <a href="index.html" class="brand" aria-label="EA Research Group home">
                <img
                    src="assets/images/earg-logo-transparent.png"
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
                <a href="founder-story.php">Founder's Story</a>
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