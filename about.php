<?php
/**
 * Legacy About URL.
 *
 * The About page has been split into standalone pages under views/About/.
 * This file is kept only so old links and bookmarks (about.php and the
 * old #who-we-are / #governing-board / #our-team / #contact-us hashes)
 * keep working. It forwards to the matching new page.
 *
 * A hash is never sent to the server, so the choice has to be made in the
 * browser. The JavaScript below does that; the <meta refresh> is the
 * fallback for visitors without JavaScript and always lands on "Who We Are".
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/site.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Redirecting | EA Research Group</title>
  <meta name="robots" content="noindex" />
  <meta http-equiv="refresh" content="0; url=<?= url('views/About/who-we-are.php') ?>">
  <script>
    (function () {
      var routes = {
        "who-we-are": "views/About/who-we-are.php",
        "governing-board": "views/About/governing-board.php",
        "our-team": "views/About/our-team.php",
        "contact-us": "views/About/contact-us.php"
      };

      var hash = window.location.hash.replace("#", "");
      var target = routes[hash] || "views/About/who-we-are.php";

      window.location.replace(target + window.location.search);
    })();
  </script>
</head>
<body>
  <p>Redirecting to the <a href="views/About/who-we-are.php">About page</a>…</p>
</body>
</html>
