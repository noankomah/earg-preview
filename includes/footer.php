<?php
/**
 * Shared public footer.
 *
 * Included at the bottom of every public page (right after the <main>
 * content). The footer is defined in this ONE file so a single change
 * here is reflected across all pages automatically.
 *
 * The year in the copyright line is filled in by the page's existing
 * JavaScript (each page sets it as it did before the restructuring).
 *
 * Links and image paths are built with url() (see includes/site.php) so
 * this footer works from the site root AND from sub-folders such as
 * views/About/.
 */

require_once __DIR__ . '/site.php';
?>
<footer class="site-footer">
    <div class="container footer-grid">

        <div class="logo-text">
            <img src="<?= url('assets/images/earg-logo-transparent.png') ?>" alt="EA Research Group Logo" class="footer-logo" />
            <p>
                Effective Altruist Research Group is a non-profit research and academic
                development organization advancing research, mentorship, innovation and impact.
            </p>
        </div>

        <div>
            <h4>Quick Links</h4>
            <a href="<?= url('views/About/who-we-are.php') ?>">Who We Are</a>
            <a href="<?= url('views/About/governing-board.php') ?>">Governing Board</a>
            <a href="<?= url('views/About/our-team.php') ?>">Our Team</a>
            <a href="<?= url('views/About/founder-story.php') ?>">Founder's Story</a>
            <a href="<?= url('views/About/contact-us.php') ?>">Contact Us</a>
        </div>

        <div>
            <h4>Find Us</h4>
            <p>Tamale, Northern Region, Ghana</p>
            <p><a href="mailto:earesearchgrp24@gmail.com">earesearchgrp24@gmail.com</a></p>

            <h4 class="follow-heading">Follow Us</h4>
            <div class="social-links">
                <!-- Placeholder until we have a Facebook page URL. A bare href="#"
                     would scroll to the top of the page, so return false to make
                     the click a no-op. Replace with the real URL when available. -->
                <a href="#" onclick="return false;">Facebook</a>
                <a href="https://www.linkedin.com/company/ea-research-group/" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="https://youtube.com/@earesearchgrp24?si=FRm8RPLxO82BLVHR" target="_blank" rel="noopener noreferrer">YouTube</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© <span id="year"></span> EA Research Group. All rights reserved.</p>
    </div>
</footer>