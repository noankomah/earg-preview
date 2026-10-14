<?php
/**
 * Who We Are - standalone About page.
 *
 * This page was split out of the old about.php so each About section now
 * has its own URL (views/About/who-we-are.php). The shared header and
 * footer are still pulled in from /includes, and links inside them are
 * built with url() so they work from this sub-folder.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/site.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Who We Are | EA Research Group</title>

  <meta name="description" content="Who we are: Effective Altruist Research Group (EARG) is a non-profit research and academic development organization based in Tamale, Ghana, working to strengthen research capacity among young scholars." />

  <link rel="stylesheet" href="<?= url('assets/css/about.css?v=3') ?>">
  <script defer src="<?= url('assets/vendor/alpinejs/alpine.min.js') ?>"></script>
</head>

<body>
  <!-- HEADER -->
  <?php include __DIR__ . '/../../includes/navbar.php'; ?>

  <main>

    <!-- WHO WE ARE -->
    <section class="about-route is-active" id="who-we-are">
      <div class="container">
        <div class="section-heading wide">
          <h1 class="eyebrow">Who We Are</h1>
          <p>
            EARG is a platform for research mentorship, academic excellence and development. We supports young scholars to grow as researchers, professionals and problem-solvers
            through structured mentorship, applied research experience and community-focused scientific work.
          </p>
        </div>

        <div class="identity-grid">
          <article class="identity-card large" id="identiy-what-we-do">
            <span>01</span>
            <h3>What we are</h3>
            <p>
              Effective Altruist Research Group is a non-profit, non-governmental,
              non-political and non-religious organization based in Tamale, Ghana.
            </p>
          </article>

          <article class="identity-card large">
            <span>02</span>
            <h3>What we do</h3>
            <p>
              We strengthen research capacity through mentorship, research training,
              academic development, consultancy, scholarship support and SDG-aligned programs.
            </p>
          </article>
        </div>

        <div class="mission-grid">
          <article class="mission-card">
            <span class="card-label">Mission</span>
            <h3>To strengthen research capacity among young scholars.</h3>
            <p>
              EARG provides structured mentorship, applied research experience and postgraduate
              preparation while delivering high-quality research and consultancy services that
              support evidence-based decision-making.
            </p>
          </article>

          <article class="mission-card dark">
            <span class="card-label">Vision</span>
            <h3>To become a leading African research and academic development institution.</h3>
            <p>
              EARG seeks to empower young scholars to excel in research, innovation and impactful
              knowledge creation while advancing sustainable development through evidence-based work.
            </p>
          </article>
        </div>

        <div class="values-block">
          <div class="section-heading compact">
            <h1 class="eyebrow">Core Values</h1>
            <h3>The principles that guide our work.</h3>
          </div>

          <div class="values-grid">
            <article class="value-card">
              <h3>Scientific Integrity</h3>
              <p>We uphold ethical, honest and evidence-informed research practice.</p>
            </article>

            <article class="value-card">
              <h3>Excellence</h3>
              <p>We pursue high standards in mentorship, research and organizational work.</p>
            </article>

            <article class="value-card">
              <h3>Transparency</h3>
              <p>We value accountability, openness and responsible decision-making.</p>
            </article>

            <article class="value-card">
              <h3>Inclusivity</h3>
              <p>We promote respect, cultural sensitivity and equal opportunity.</p>
            </article>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script src="<?= url('assets/js/about-pages.js') ?>"></script>
</body>
</html>
