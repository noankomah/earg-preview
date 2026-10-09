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
 */
?>
<header class="site-header" x-data="{ open: false }">
    <div class="container header-inner">

        <a href="index.php" class="brand" aria-label="EA Research Group home">
            <img src="assets/images/earg-logo-transparent.png" alt="EA Research Group Logo" class="brand-logo" />
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

            <a href="index.php">Home</a>

            <div class="nav-dropdown">
                <button type="button" class="nav-parent">About Us</button>
                <div class="dropdown-menu">
                    <a href="about.php#who-we-are" data-route-link="who-we-are">Who We Are</a>
                    <a href="about.php#governing-board" data-route-link="governing-board">Governing Board</a>
                    <a href="about.php#our-team" data-route-link="our-team">Our Team</a>
                    <a href="founder-story.php">Founder's Story</a>

                    <!-- Work With Us and Our Partners are hidden until their pages are ready.
                    <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Work With Us</a>
                    <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Our Partners</a>
                    -->
                    <a href="about.php#contact-us" data-route-link="contact-us">Contact Us</a>
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
                    <a href="publications.php#blogs" data-route-link="blogs">Blogs</a>
                    <a href="publications.php#newsletters" data-route-link="newsletters">Newsletters</a>
                    <a href="publications.php#reports" data-route-link="reports">Reports</a>
                    <a href="publications.php#scholarship-opportunities" data-route-link="scholarship-opportunities">
                        Scholarship & Opportunities
                    </a>
                </div>
            </div>

            <a href="#" class="inactive-link" aria-disabled="true" onclick="return false;">Events</a>
        </nav>

    </div>
</header>