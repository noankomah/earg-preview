<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Events | EA Research Group</title>
  <meta name="description" content="EA Research Group events page placeholder." />

  <link rel="stylesheet" href="assets/css/about.css" />
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
  <!-- HEADER -->
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <main class="empty-page" aria-label="Events">
    <!--
      Events content will be added later.
      This placeholder file exists so the page is ready,
      but navigation links are intentionally inactive for now.
    -->
  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const year = document.getElementById("year");
      if (year) year.textContent = new Date().getFullYear();
    });
  </script>
</body>
</html>
