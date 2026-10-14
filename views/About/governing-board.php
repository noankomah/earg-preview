<?php
/**
 * Governing Board - standalone About page.
 *
 * Split out of the old about.php. Board member photos use url() so the
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

  <title>Governing Board | EA Research Group</title>

  <meta name="description" content="The governing board of Effective Altruist Research Group (EARG) provides strategic oversight, policy direction, accountability and institutional guidance." />

  <link rel="stylesheet" href="<?= url('assets/css/about.css?v=3') ?>">
  <script defer src="<?= url('assets/vendor/alpinejs/alpine.min.js') ?>"></script>
</head>

<body>
  <!-- HEADER -->
  <?php include __DIR__ . '/../../includes/navbar.php'; ?>

  <main>

    <!-- GOVERNING BOARD -->
    <section class="about-route" id="governing-board">
      <div class="container" id="gorvenment">
        <div class="section-heading centered">
          <h1>Governing Board</h1>
          <p>
            The Governing Board provides strategic oversight, policy direction,
            accountability and institutional guidance for EARG.
          </p>
        </div>

        <div class="board-grid board-five" aria-label="Governing Board members">
          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/Jacob.png') ?>" id="jacob-img">
            </div>
            <h3>Dr. Jacob Achumboro Ayang</h3>
            <p>Board Chair</p>
          </article>

          <article class="person-card">
            <div class="person-photo" id="lucky">
              <img src="<?= url('assets/images/lucky.png') ?>" id="lucky-img">
            </div>
            <h3>Lucky Adeline Aanomah</h3>
            <p>Deputy Chair</p>
          </article>

          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/sandra.png') ?>" id="sandra-img">
            </div>
            <h3>Sandra Sore</h3>
            <p>Secretary</p>
          </article>

          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/Emma.jpg') ?>" id="emma-img">
            </div>
            <h3>Emmanuel Adom</h3>
            <p>Member</p>
          </article>

          <article class="person-card">
            <div class="person-photo">
              <img src="<?= url('assets/images/nana2.png') ?>" id="nana-img">
            </div>
            <h3>Nana Okai Ankomah</h3>
            <p>Member</p>
          </article>
        </div>
        <!--
        <div class="advisory-block">
          <div class="section-heading centered small-gap">
            <h1>Advisory Board</h1>
            <p>
              The Advisory Board provides expert guidance, institutional advice and
              strategic support to strengthen EARG’s programs and long-term direction.
            </p>
          </div>

          <div class="advisory-grid" aria-label="Advisory Board members">
            <article class="person-card">
              <div class="person-photo"><span>Photo</span></div>
              <h3>Advisor Name</h3>
              <p>Advisory Board Member</p>
            </article>

            <article class="person-card">
              <div class="person-photo"><span>Photo</span></div>
              <h3>Advisor Name</h3>
              <p>Advisory Board Member</p>
            </article>
          </div>
        </div>
        -->
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script src="<?= url('assets/js/about-pages.js') ?>"></script>
</body>
</html>
