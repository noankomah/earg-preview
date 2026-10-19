<?php
/**
 * Contact Us - standalone About page.
 *
 * Layout:  Bootstrap grid (row / columns) for the two-column arrangement.
 * Look:    the site's existing .contact-* styles in assets/css/about.css,
 *          so this page matches the original About page (white detail
 *          cards, large green heading, soft-shadowed form card, gold focus
 *          ring). assets/css/contact.css only glues the Bootstrap grid to
 *          those original proportions.
 *
 * The form posts to contact.php, which saves the message and redirects
 * back with ?status=sent or ?status=error; about-pages.js shows the notice.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/site.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Contact Us | EA Research Group</title>

  <meta name="description" content="Contact EA Research Group for partnerships, mentorship, research collaboration, consultancy, events or general enquiries. Based in Tamale, Northern Region, Ghana." />

  <!-- Bootstrap first, then the shared site styles, then this page's own CSS. -->
  <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/about.css?v=5') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/contact.css?v=3') ?>">
  <script defer src="<?= url('assets/vendor/alpinejs/alpine.min.js') ?>"></script>
</head>

<body>
  <!-- HEADER -->
  <?php include __DIR__ . '/../../includes/navbar.php'; ?>

  <main>
    <section class="about-route">
      <div class="container">
        <div class="row align-items-start contact-layout">

          <!-- CONTACT DETAILS -->
          <div class="col-12 col-lg-6 contact-info">
            <h2>Reach out to EA Research Group.</h2>
            <p>
              For partnerships, mentorship, research collaboration, consultancy,
              events or general enquiries, contact us through the details below.
            </p>

            <div class="contact-list">
              <div>
                <span>Email</span>
                <a href="mailto:earesearchgrp24@gmail.com">earesearchgrp24@gmail.com</a>
              </div>

              <div>
                <span>Location</span>
                <p>Tamale, Northern Region, Ghana</p>
              </div>

              <div>
                <span>Focus Areas</span>
                <p>Research mentorship, academic development, consultancy, scholarships and community-based scientific development.</p>
              </div>
            </div>
          </div>

          <!-- MESSAGE FORM -->
          <div class="col-12 col-lg-6 contact-form-col">
            <form class="contact-form" action="<?= url('contact.php') ?>" method="post">
              <h3>Send a message</h3>

              <!-- Notice shown after the form is submitted -->
              <p id="contactStatus" class="contact-status" hidden></p>

              <label>
                Full Name
                <input type="text" name="name" required />
              </label>

              <label>
                Email Address
                <input type="email" name="email" required />
              </label>

              <label>
                Message
                <textarea name="message" rows="6" required></textarea>
              </label>

              <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
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
