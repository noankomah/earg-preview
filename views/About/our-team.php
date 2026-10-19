<?php
/**
 * Our Team - standalone About page.
 *
 * Split out of the old about.php. The cards are read from the team_members
 * table and managed in the admin area (admin/team/), so adding someone never
 * needs a code change. Photo paths are passed through url() so they resolve
 * correctly from this sub-folder.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/site.php';
require_once __DIR__ . '/../../admin/includes/db.php';

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

/*
 * Only visible members are shown, in display order (ascending).
 *
 * The query is wrapped in try/catch so a database that has not had
 * admin/team/schema.sql imported yet still renders this page with a short
 * note instead of a server error page.
 */
$members = [];

try {
    $statement = db()->query(
        "
        SELECT
            name,
            role_title,
            photo_url
        FROM team_members
        WHERE is_visible = 1
        ORDER BY display_order ASC, id ASC
        "
    );

    $members = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log('Public Our Team page error: ' . $exception->getMessage());
}

// Shown when a member has no photo uploaded yet.
$defaultPhoto = 'assets/images/earg-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Our Team | EA Research Group</title>

  <meta name="description" content="Meet the executive team of Effective Altruist Research Group (EARG), who lead day-to-day implementation, administration, coordination and operational delivery." />

  <link rel="stylesheet" href="<?= url('assets/css/about.css?v=5') ?>">
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

        <?php if ($members): ?>
          <div class="team-grid" aria-label="Executive team members">
            <?php foreach ($members as $member): ?>
              <article class="person-card">
                <div class="person-photo">
                  <img
                    src="<?= url($member['photo_url'] !== null && $member['photo_url'] !== ''
                      ? (string) $member['photo_url']
                      : $defaultPhoto) ?>"
                    alt="Portrait of <?= e((string) $member['name']) ?>"
                  >
                </div>

                <h3><?= e((string) $member['name']) ?></h3>

                <?php if ($member['role_title'] !== null && $member['role_title'] !== ''): ?>
                  <p><?= e((string) $member['role_title']) ?></p>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="section-heading centered">
            <p>Our team profiles are being updated. Please check back soon.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script src="<?= url('assets/js/about-pages.js') ?>"></script>
</body>
</html>
