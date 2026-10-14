<?php
/**
 * Shared public navigation bar.
 *
 * This header (logo, mobile menu and main navigation) is included on
 * every public page from this ONE file. Changing the menu here updates
 * it automatically across all pages that use it.
 *
 * Pages are linked as .php because the static .html pages were converted
 * to PHP pages so this include (and the shared footer) can be used.
 *
 * Links and image paths are built with url() (see includes/site.php) so
 * this bar works from the site root AND from sub-folders such as
 * views/About/.
 */

require_once __DIR__ . '/site.php';
?>
<header class="site-header" x-data="{ open: false }">
    <div class="container header-inner">

        <a href="<?= url('index.php') ?>" class="brand" aria-label="EA Research Group home">
            <img src="<?= url('assets/images/earg-logo.png') ?>" alt="EA Research Group Logo" class="brand-logo" />
            <span class="brand-text">
                <strong>EA Research Group</strong>
                <small>Research • Mentorship • Innovation • Impact</small>
            </span>
        </a>

        <button class="menu-toggle" @click="open = !open" :aria-expanded="open.toString()" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="main-nav" :class="{ 'is-open': open }" aria-label="Primary navigation">

            <a href="<?= url('index.php') ?>">Home</a>

            <div class="nav-dropdown">
                <button type="button" class="nav-parent">About Us</button>
                <div class="dropdown-menu">
                    <a href="<?= url('views/About/who-we-are.php') ?>">Who We Are</a>
                    <a href="<?= url('views/About/governing-board.php') ?>">Governing Board</a>
                    <a href="<?= url('views/About/our-team.php') ?>">Our Team</a>
                    <a href="<?= url('views/About/founder-story.php') ?>">Founder's Story</a>

                    <!-- Work With Us and Our Partners are hidden until their pages are ready.
                    <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Work With Us</a>
                    <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Our Partners</a>
                    -->
                    <a href="<?= url('views/About/contact-us.php') ?>">Contact Us</a>
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
                    <a href="<?= url('publications.php#blogs') ?>" data-route-link="blogs">Blogs</a>
                    <a href="<?= url('publications.php#newsletters') ?>" data-route-link="newsletters">Newsletters</a>
                    <a href="<?= url('publications.php#reports') ?>" data-route-link="reports">Reports</a>
                    <a href="<?= url('publications.php#scholarship-opportunities') ?>" data-route-link="scholarship-opportunities">
                        Scholarship & Opportunities
                    </a>
                </div>
            </div>

            <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Events</a>
        </nav>

    </div>
</header>