<?php
/**
 * Our Team - standalone About page.
 *
 * Split out of the old about.php. Team member photos use url() so the
 * image paths resolve correctly from this sub-folder.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/site.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Our Team | EA Research Group</title>

  <meta name="description" content="Meet the executive team of Effective Altruist Research Group (EARG), who lead day-to-day implementation, administration, coordination and operational delivery." />

  <link rel="stylesheet" href="<?= url('assets/css/about.css?v=3') ?>">
  <script defer src="<?= url('assets/vendor/alpinejs/alpine.min.js') ?>"></script>
</head>

<body>
  <!-- HEADER -->
  <?php include __DIR__ . '/../../includes/navbar.php'; ?>

  <main>

    <!-- OUR TEAM -->
    <section class="about-route" id="our-team">
      <div class="container">
        <div class="section-heading centered">
          <h2>Our Team</h2>
          <p>
            The executive team leads day-to-day implementation, administration,
            coordination and operational delivery of EARG’s programs.
          </p>
        </div>

        <div class="team-grid" aria-label="Executive team members">
          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/Emma.jpg') ?>" id="emma-img">
            </div>
            <h3>Emmanuel Adom</h3>
            <p>Executive Director</p>
          </article>

          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/kahar.jpg') ?>" id="nana-img">
            </div>
            <h3>Abdul Kahar Abdul Rahman Gunu</h3>
            <p>Operations Officer</p>
          </article>

          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/nana2.png') ?>" id="nana-img">
            </div>
            <h3>Nana Ankomah</h3>
            <p>Digital and Communications Lead</p>
          </article>
        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script src="<?= url('assets/js/about-pages.js') ?>"></script>
</body>
</html>
