<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Publications | EA Research Group</title>
    <meta
      name="description"
      content="EA Research Group blogs, newsletters, reports, scholarships and opportunities."
    />

    <link rel="stylesheet" href="assets/css/about.css" />
    <link rel="stylesheet" href="assets/css/publications.css" />
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </head>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const dropdownButtons = document.querySelectorAll(".nav-parent");
      const routeLinks = document.querySelectorAll("[data-route-link]");
      const mainNav = document.querySelector(".main-nav");
      const menuToggle = document.querySelector(".menu-toggle");

      dropdownButtons.forEach(function (button) {
        button.addEventListener("click", function () {
          if (window.innerWidth > 1024) return;

          const currentDropdown = button.closest(".nav-dropdown");
          if (!currentDropdown) return;

          document
            .querySelectorAll(".nav-dropdown.is-open")
            .forEach(function (dropdown) {
              if (dropdown !== currentDropdown) {
                dropdown.classList.remove("is-open");
              }
            });

          currentDropdown.classList.toggle("is-open");
        });
      });

      routeLinks.forEach(function (link) {
        link.addEventListener("click", function () {
          if (window.innerWidth <= 1024) {
            if (mainNav) {
              mainNav.classList.remove("is-open");
            }

            document
              .querySelectorAll(".nav-dropdown.is-open")
              .forEach(function (dropdown) {
                dropdown.classList.remove("is-open");
              });

            if (menuToggle) {
              menuToggle.setAttribute("aria-expanded", "false");
            }
          }
        });
      });
    });
  </script>

  <body>
    <!-- HEADER -->
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main aria-label="Publications">

      <!-- =====================================================
          BLOGS SECTION
          Route: publications.php#blogs
      ====================================================== -->
      <section id="blogs" class="about-route">
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Blogs</p>
            <h3>Ideas, perspectives and emerging issues</h3>
            <p>
              Read articles, reflections and informed perspectives from researchers,
              professionals and contributors on topics related to research, mentorship,
              innovation and community development.
            </p>
          </div>

          <div class="values-grid">
            <article class="value-card">
              <h3>Research and development perspectives</h3>
              <p>
                Reflections on how research can respond to community needs,
                development priorities and emerging social challenges.
              </p>
            </article>

            <article class="value-card">
              <h3>Mentorship and academic growth</h3>
              <p>
                Writings on student mentorship, postgraduate preparation,
                research culture and early-career development.
              </p>
            </article>

            <article class="value-card">
              <h3>Innovation and impact</h3>
              <p>
                Articles on practical innovation, institutional growth and how ideas
                can be translated into measurable social impact.
              </p>
            </article>

            <article class="value-card">
              <h3>Trending issues</h3>
              <p>
                Commentary on relevant trends connected to education, research,
                policy, science and community development.
              </p>
            </article>
          </div>
        </div>
      </section>


      <!-- =====================================================
          NEWSLETTERS SECTION
          Route: publications.php#newsletters
      ====================================================== -->
      <section id="newsletters" class="about-route">
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Newsletters</p>
            <h3>EA Research Group news and updates</h3>
            <p>
              Follow updates on our programmes, partnerships, events, opportunities,
              research activities and institutional development.
            </p>
          </div>

          <div class="values-grid">
            <article class="value-card">
              <h3>Monthly updates</h3>
              <p>
                Updates on EARG activities, organisational development and programme
                progress.
              </p>
            </article>

            <article class="value-card">
              <h3>Opportunities round-up</h3>
              <p>
                A curated summary of selected scholarships, fellowships, internships
                and academic opportunities.
              </p>
            </article>

            <article class="value-card">
              <h3>Programme highlights</h3>
              <p>
                Highlights from mentorship activities, research capacity programmes
                and community-focused initiatives.
              </p>
            </article>

            <article class="value-card">
              <h3>Upcoming activities</h3>
              <p>
                Notices on upcoming calls, events, meetings, training sessions and
                other important dates.
              </p>
            </article>
          </div>
        </div>
      </section>


      <!-- =====================================================
          REPORTS SECTION
          Route: publications.php#reports
      ====================================================== -->
      <section id="reports" class="about-route">
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Reports</p>
            <h3>Reports, briefs and institutional publications</h3>
            <p>
              Access research reports, annual reports, financial reports, policy
              briefs and other institutional publications from EA Research Group.
            </p>
          </div>

          <div class="values-grid">
            <article class="value-card">
              <h3>Research reports</h3>
              <p>
                Reports from research projects, field activities, needs assessments
                and evidence-gathering work.
              </p>
            </article>

            <article class="value-card">
              <h3>Annual reports</h3>
              <p>
                Yearly summaries of EARG activities, achievements, partnerships and
                institutional progress.
              </p>
            </article>

            <article class="value-card">
              <h3>Financial reports</h3>
              <p>
                Financial accountability documents, summaries and reports prepared
                for transparency and institutional reporting.
              </p>
            </article>

            <article class="value-card">
              <h3>Policy and learning briefs</h3>
              <p>
                Short publications that communicate lessons, recommendations and
                insights from research or programme work.
              </p>
            </article>
          </div>
        </div>
      </section>


      <!-- =====================================================
          SCHOLARSHIP & OPPORTUNITIES SECTION
          Route: publications.php#scholarship-opportunities
      ====================================================== -->
      <section id="scholarship-opportunities" class="about-route">
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Scholarship & Opportunities</p>
            <h3>Scholarships, fellowships, internships and research opportunities</h3>
            <p>
              Explore widely available scholarships, fellowships, internships, grants,
              conferences, trainings and other academic or professional opportunities
              for students, graduates and early-career researchers.
            </p>
          </div>

          <div class="values-grid">
            <article class="value-card">
              <h3>Scholarships</h3>
              <p>
                Undergraduate, postgraduate and research-focused scholarship
                opportunities from institutions and funding bodies.
              </p>
            </article>

            <article class="value-card">
              <h3>Fellowships</h3>
              <p>
                Fellowship opportunities for students, graduates, researchers and
                early-career professionals.
              </p>
            </article>

            <article class="value-card">
              <h3>Internships and training</h3>
              <p>
                Internship, training, summer school and capacity-building
                opportunities relevant to young researchers.
              </p>
            </article>

            <article class="value-card">
              <h3>Grants and calls</h3>
              <p>
                Calls for applications, research grants, conferences, workshops and
                other academic opportunities.
              </p>
            </article>
          </div>
        </div>
      </section>

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
