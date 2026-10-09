<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>About Us | EA Research Group</title>

  <meta name="description" content="Learn about Effective Altruist Research Group, its mission, vision, governing board, advisory board, executive team and contact information." />

  <link rel="stylesheet" href="assets/css/about.css?v=2">

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

  document.addEventListener("DOMContentLoaded", function () {
    const routeLinks = document.querySelectorAll("[data-route-link]");
    const mainNav = document.querySelector(".main-nav");
    const menuToggle = document.querySelector(".menu-toggle");

    routeLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.innerWidth <= 1024) {
          /* Close the main mobile menu */
          if (mainNav) {
            mainNav.classList.remove("is-open");
          }

          /* Close any open dropdown */
          document
            .querySelectorAll(".nav-dropdown.is-open")
            .forEach(function (dropdown) {
              dropdown.classList.remove("is-open");
            });

          /* Reset the hamburger button */
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

  <main>

    <!-- ROUTED PAGE CONTENT -->
    <div class="about-route-wrap" id="aboutRouteWrap">

      <!-- WHO WE ARE -->
      <section class="about-route is-active" id="who-we-are" data-route="who-we-are">
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
              <h3>The principles that guide our work.</h2>
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

      <!-- GOVERNING BOARD -->
      <section class="about-route" id="governing-board" data-route="governing-board" hidden>
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
                <img src="assets/images/Jacob.png" id="jacob-img">
              </div>
              <h3>Dr. Jacob Achumboro Ayang</h3>
              <p>Board Chair</p>
            </article>

            <article class="person-card">
              <div class="person-photo" id="lucky">
                <img src="assets/images/lucky.png" id="lucky-img">
              </div>
              <h3>Lucky Adeline Aanomah</h3>
              <p>Deputy Chair</p>
            </article>

            <article class="person-card">
              <div class="person-photo">
                <img src="assets/images/sandra.png" id="sandra-img">
              </div>
              <h3>Sandra Sore</h3>
              <p>Secretary</p>
            </article>

            <article class="person-card">
              <div class="person-photo">
                <img src="assets/images/Emma.jpg" id="emma-img">
              </div>
              <h3>Emmanuel Adom</h3>
              <p>Member</p>
            </article>

            <article class="person-card">
              <div class="person-photo">
                <img src="assets/images/nana2.png" id="nana-img">
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

      <!-- OUR TEAM -->
      <section class="about-route" id="our-team" data-route="our-team" hidden>
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
                <img src="assets/images/Emma.jpg" id="emma-img">
              </div>
              <h3>Emmanuel Adom</h3>
              <p>Executive Director</p>
            </article>

            
            <article class="person-card">
              <div class="person-photo">
                <img src="assets/images/kahar.jpg" id="nana-img">
              </div>
              <h3>Abdul Kahar Abdul Rahman Gunu</h3>
              <p>Operations Officer</p>
            </article>
            
            <article class="person-card">
              <div class="person-photo">
                <img src="assets/images/nana2.png" id="nana-img">
              </div>
              <h3>Nana Ankomah</h3>
              <p>Human Resources Manager</p>
            </article>
          </div>
        </div>
      </section>

      <!-- CONTACT US -->
      <section class="about-route" id="contact-us" data-route="contact-us" hidden>
        <div class="container contact-grid">
          <div class="contact-info">
            <!--<p class="eyebrow">Contact Us</p> -->
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

          <form class="contact-form" action="contact.php" method="post">
            <h3>Send a message</h3>

            <!-- Small notice shown after the form is submitted -->
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
      </section>

    </div>
  </main>

  <!-- FOOTER -->
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const year = document.getElementById("year");
      const routeWrap = document.getElementById("aboutRouteWrap");
      const routes = Array.from(document.querySelectorAll("[data-route]"));
      const routeLinks = Array.from(document.querySelectorAll("[data-route-link]"));

      const routeTitles = {
        "who-we-are": "Who We Are | EA Research Group",
        "governing-board": "Governing Board | EA Research Group",
        "our-team": "Our Team | EA Research Group",
        "contact-us": "Contact Us | EA Research Group"
      };

      if (year) {
        year.textContent = new Date().getFullYear();
      }

      function getCurrentRoute() {
        const hash = window.location.hash.replace("#", "");
        return routes.some((section) => section.dataset.route === hash) ? hash : "who-we-are";
      }

      function showRoute(shouldScroll) {
        const activeRoute = getCurrentRoute();

        routes.forEach((section) => {
          const isActive = section.dataset.route === activeRoute;
          section.hidden = !isActive;
          section.classList.toggle("is-active", isActive);
        });

        routeLinks.forEach((link) => {
          link.classList.toggle("is-active", link.dataset.routeLink === activeRoute);
        });

        document.title = routeTitles[activeRoute] || "About Us | EA Research Group";

        if (shouldScroll && routeWrap) {
          routeWrap.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      }

      showRoute(false);

      window.addEventListener("hashchange", function () {
        showRoute(true);
      });

      // Show a thank-you / error message after the contact form is submitted.
      // The form posts to contact.php, which redirects back here with
      // ?status=sent or ?status=error in the URL.
      const contactStatus = document.getElementById("contactStatus");

      if (contactStatus) {
        const status = new URLSearchParams(window.location.search).get("status");

        if (status === "sent") {
          contactStatus.textContent =
            "Thank you! Your message has been sent. We will get back to you soon.";
          contactStatus.hidden = false;
        } else if (status === "error") {
          contactStatus.textContent =
            "Sorry, something went wrong. Please try again or email us directly.";
          contactStatus.hidden = false;
        }
      }
    });
    function setHeaderOffset() {
    const header = document.querySelector(".site-header");
    const headerHeight = header ? header.offsetHeight : 78;

    document.documentElement.style.setProperty(
        "--header-offset",
        `${headerHeight + 16}px`
    );
    }

    window.addEventListener("load", setHeaderOffset);
    window.addEventListener("resize", setHeaderOffset);
  </script>
</body>
</html>