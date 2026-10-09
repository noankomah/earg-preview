<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>EA Research Group | Research, Mentorship, Innovation and Impact</title>

  <meta name="description" content="Effective Altruist Research Group is a non-profit research and academic development organization based in Tamale, Ghana." />

  <link rel="stylesheet" href="assets/css/index.css?v=2">

  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".nav-dropdown");

    dropdowns.forEach(function (dropdown) {
      const parent = dropdown.querySelector(".nav-parent");

      if (!parent) return;

      parent.addEventListener("click", function (event) {
        if (window.innerWidth > 1024) return;

        event.preventDefault();

        dropdowns.forEach(function (otherDropdown) {
          if (otherDropdown !== dropdown) {
            otherDropdown.classList.remove("is-open");
          }
        });

        dropdown.classList.toggle("is-open");
      });
    });
  });
</script>

<body>
  <!-- HEADER -->
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <main>
    <!-- FULL-WIDTH HERO SLIDER -->
   <!-- FULL-WIDTH HERO SLIDER -->
    <section class="hero-slider" aria-label="EA Research Group highlights">
      <div class="slide-stage" id="heroSlider"></div>

      <div class="hero-fixed-overlay">
        <div class="container slide-content">
          
          <div class="hero-actions">
          </div>
        </div>
      </div>

      <button class="slider-btn slider-btn-left" id="prevSlide" aria-label="Previous slide">‹</button>
      <button class="slider-btn slider-btn-right" id="nextSlide" aria-label="Next slide">›</button>

      <div class="slider-dots" id="sliderDots" aria-label="Slider navigation"></div>
    </section>


    <!-- WHO WE ARE -->
    <section class="who-section">
      <div class="container who-grid">
        
        <div class="who-content">
          <h1 class="eyebrow">Who we are</h1>

          <p class="who-lead">
            Effective Altruist Research Group is a non-profit, non-governmental,
            non-political and non-religious organization based in Tamale, Ghana.
            We exist to strengthen research capacity among young scholars while
            supporting evidence-based research and community-based scientific development.
          </p>

          <p>
            Through mentorship, applied research experience, consultancy, scholarship
            support and strategic partnerships, EARG creates pathways for young people
            to grow as researchers, professionals and problem-solvers.
          </p>

          <div class="who-actions">
            <a href="about.php#who-we-are" class="btn btn-primary">Learn More</a>
            <a href="about.php#contact-us" class="btn btn-secondary">Work With Us</a>
          </div>
        </div>

        <div class="who-panel">
          <div class="who-panel-header">
            <span>Our Core Direction</span>
           
          </div>

          <div class="who-mini-grid">
            <div class="who-mini-card">
              <strong>Mentorship</strong>
              <p>Guiding young scholars into research and postgraduate pathways.</p>
            </div>

            <div class="who-mini-card">
              <strong>Research</strong>
              <p>Supporting ethical, applied and policy-relevant research.</p>
            </div>

            <div class="who-mini-card">
              <strong>Consultancy</strong>
              <p>Delivering research, evaluation and advisory services.</p>
            </div>

            <div class="who-mini-card">
              <strong>SDG Impact</strong>
              <p>Aligning education, health, work, environment and partnerships.</p>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- WHAT WE DO -->
    <section class="section muted">
      <div class="container">
        <div class="section-heading">
          <h1 class="eyebrow">What we do</h1>
          <h2>Programs designed for research, mentorship, innovation and community impact.</h2>
        </div>

        <div class="card-grid four">
          <article class="info-card">
            <span class="icon">🎓</span>
            <h3>Mentorship and Fellowship</h3>
            <p>
              Structured support for final-year students, graduates and emerging
              researchers interested in academic and research careers.
            </p>
          </article>

          <article class="info-card">
            <span class="icon">🔬</span>
            <h3>Research Capacity</h3>
            <p>
              Training in research methods, data collection, data analysis,
              scientific writing, ethics and academic professionalism.
            </p>
          </article>

          <article class="info-card">
            <span class="icon">🌿</span>
            <h3>Community Research</h3>
            <p>
              Research and engagement in public health, medicinal plants,
              biodiversity conservation and environmental sustainability.
            </p>
          </article>

          <article class="info-card">
            <span class="icon">🤝</span>
            <h3>Consultancy and Partnerships</h3>
            <p>
              Research, evaluation and advisory services that support evidence-based
              decisions and help sustain scholarships and programs.
            </p>
          </article>
        </div>
      </div>
    </section>

  

   <!-- LATEST NEWS AND PUBLICATIONS -->
    <section class="section">
      <div class="container">
        <div class="section-heading row-heading">
          <div>
            <h1 class="eyebrow">Latest updates</h1>
            <h2>News and publications</h2>
          </div>
        </div>

        <div class="news-grid">
          <article class="news-card">
            <p class="news-label">Scholarship & Opportunities</p>

            <h3>Scholarships, fellowships and opportunities</h3>

            <p>
              Explore widely available scholarships, fellowships, internships, grants
              and other academic and professional opportunities for students,
              graduates and early-career researchers.
            </p>

            <a href="publications.php#scholarship-opportunities">
              Explore opportunities →
            </a>
          </article>

          <article class="news-card">
            <p class="news-label">Blogs</p>

            <h3>Ideas, perspectives and emerging issues</h3>

            <p>
              Read articles, reflections and informed perspectives from researchers
              and professionals on topics related to research, mentorship,
              innovation and community development.
            </p>

            <a href="publications.php#blogs">
              Read our blogs →
            </a>
          </article>

          <article class="news-card">
            <p class="news-label">Newsletters</p>

            <h3>EA Research Group news and updates</h3>

            <p>
              Follow updates on our programmes, partnerships, events, opportunities,
              research activities and institutional development.
            </p>

            <a href="publications.php#newsletters">
              Read our newsletters →
            </a>
          </article>
        </div>
      </div>
    </section>

    <!-- NEWSLETTER -->
    <section class="newsletter-section">
      <div class="container newsletter-box">
        <div>
          <p class="eyebrow">Stay connected</p>
          <h2>Subscribe to our newsletter</h2>
          <p>
            Receive updates on mentorship opportunities, programs, publications,
            events and research activities.
          </p>
        </div>

        <form class="newsletter-form">
          <input type="email" name="email" placeholder="Enter your email address" required />
          <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
        <p id="newsletterMessage" class="newsletter-message" hidden></p>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script src="assets/js/index.js"></script>
</body>
</html>