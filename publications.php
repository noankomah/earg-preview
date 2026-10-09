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

    <link rel="stylesheet" href="assets/css/about.css?v=2">
    <link rel="stylesheet" href="assets/css/publications.css?v=4">  
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </head>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const dropdownButtons = document.querySelectorAll(".nav-parent");
      const routeLinks = document.querySelectorAll("[data-route-link]");
      const mainNav = document.querySelector(".main-nav");
      const menuToggle = document.querySelector(".menu-toggle");

      /*
        MOBILE DROPDOWN MENU
        Opens/closes About Us, Programs, Publications on mobile.
      */
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

      /*
        CLOSE MOBILE MENU AFTER CLICKING A LINK
      */
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

      /*
        PUBLICATIONS PAGE ROUTING
        One file: publications.php
        Different page views: #blogs, #newsletters, #reports, #scholarship-opportunities
      */
      const publicationRoutes = document.querySelectorAll("[data-publication-route]");

      function showPublicationRoute() {
        if (!publicationRoutes.length) return;

        let route = window.location.hash.replace("#", "");

        if (!route) {
          route = "blogs";
          history.replaceState(null, "", "publications.php#blogs");
        }

        publicationRoutes.forEach(function (section) {
          section.hidden = section.id !== route;
        });

        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
      }

      showPublicationRoute();
      window.addEventListener("hashchange", showPublicationRoute);

      const opportunitiesGrid = document.getElementById("public-opportunities-grid");
      const opportunitiesLoading = document.getElementById("opportunities-loading");
      const opportunitiesError = document.getElementById("opportunities-error");
      const opportunitiesEmpty = document.getElementById("opportunities-empty");
      const opportunitiesCarousel = document.getElementById("opportunities-carousel");
      const opportunitiesPrevious = document.getElementById("opportunities-previous");
      const opportunitiesNext = document.getElementById("opportunities-next");

      let opportunitiesLoaded = false;

      function escapeHtml(value) {
        return String(value ?? "")
          .replaceAll("&", "&amp;")
          .replaceAll("<", "&lt;")
          .replaceAll(">", "&gt;")
          .replaceAll('"', "&quot;")
          .replaceAll("'", "&#039;");
      }

      function formatOpportunityDate(dateValue) {
        if (!dateValue) {
          return "Not specified";
        }

        const date = new Date(`${dateValue}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
          return dateValue;
        }

        return new Intl.DateTimeFormat("en-GB", {
          day: "numeric",
          month: "long",
          year: "numeric"
        }).format(date);
      }

      function showOpportunityState(state) {
        if (opportunitiesLoading) {
          opportunitiesLoading.hidden = state !== "loading";
        }

        if (opportunitiesError) {
          opportunitiesError.hidden = state !== "error";
        }

        if (opportunitiesEmpty) {
          opportunitiesEmpty.hidden = state !== "empty";
        }

        if (opportunitiesGrid) {
          opportunitiesGrid.hidden = state !== "success";
        }
        if (opportunitiesCarousel) {
          opportunitiesCarousel.hidden = state !== "success";
        }
      }

      function createOpportunityCard(opportunity) {
        const article = document.createElement("article");
        article.className = "opportunity-card";

        if (Number(opportunity.is_featured) === 1) {
          article.classList.add("is-featured");
        }

        const locationText =
          opportunity.country ||
          opportunity.host_organization ||
          "";

        article.innerHTML = `
          <div class="opportunity-card-heading">
            <p class="opportunity-type">
              ${escapeHtml(opportunity.category)}
            </p>

            ${
              Number(opportunity.is_featured) === 1
                ? '<span class="opportunity-featured-badge">Featured</span>'
                : ""
            }
          </div>

          <h3>${escapeHtml(opportunity.title)}</h3>

          <p class="opportunity-summary">
            ${escapeHtml(opportunity.summary)}
          </p>

          <div class="opportunity-meta">
            <span>
              <strong>Deadline:</strong>
              ${escapeHtml(formatOpportunityDate(opportunity.deadline))}
            </span>

            <span>
              <strong>Status:</strong>
              ${escapeHtml(opportunity.status)}
            </span>

            ${
              locationText
                ? `
                  <span>
                    <strong>Location/Host:</strong>
                    ${escapeHtml(locationText)}
                  </span>
                `
                : ""
            }
          </div>

          <a
            href="opportunity.php?slug=${encodeURIComponent(opportunity.slug)}"
            class="read-more-link"
          >
            Read more →
          </a>
        `;

        return article;
      }

      async function loadOpportunities() {
        if (opportunitiesLoaded || !opportunitiesGrid) {
          return;
        }

        opportunitiesLoaded = true;
        showOpportunityState("loading");

        try {
          const response = await fetch("api/opportunities.php", {
            headers: {
              Accept: "application/json"
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
          }

          const data = await response.json();

          if (!data.success || !Array.isArray(data.opportunities)) {
            throw new Error("Invalid API response");
          }

          opportunitiesGrid.innerHTML = "";

          if (data.opportunities.length === 0) {
            showOpportunityState("empty");
            return;
          }

          data.opportunities.forEach(function (opportunity) {
            opportunitiesGrid.appendChild(
              createOpportunityCard(opportunity)
            );
          });

          showOpportunityState("success");
        } catch (error) {
          console.error("Unable to load opportunities:", error);
          showOpportunityState("error");
        }
      }

      function loadOpportunitiesForCurrentRoute() {
        const route = window.location.hash.replace("#", "");

        if (route === "scholarship-opportunities") {
          loadOpportunities();
        }
      }

      function scrollOpportunityCarousel(direction) {
        if (!opportunitiesGrid) return;

        const card = opportunitiesGrid.querySelector(".opportunity-card");

        const scrollDistance = card
          ? card.getBoundingClientRect().width + 20
          : opportunitiesGrid.clientWidth * 0.8;

        opportunitiesGrid.scrollBy({
          left: direction * scrollDistance,
          behavior: "smooth"
        });
      }

      if (opportunitiesPrevious) {
        opportunitiesPrevious.addEventListener("click", function () {
          scrollOpportunityCarousel(-1);
        });
      }

      if (opportunitiesNext) {
        opportunitiesNext.addEventListener("click", function () {
          scrollOpportunityCarousel(1);
        });
      }

      loadOpportunitiesForCurrentRoute();

      window.addEventListener(
        "hashchange",
        loadOpportunitiesForCurrentRoute
      );


      const publicationSettings = {
        blogs: {
          type: "Blog",
          gridId: "public-blogs-grid",
          loadingId: "blogs-loading",
          errorId: "blogs-error",
          emptyId: "blogs-empty",
          carouselId: "blogs-carousel"
        },
        newsletters: {
          type: "Newsletter",
          gridId: "public-newsletters-grid",
          loadingId: "newsletters-loading",
          errorId: "newsletters-error",
          emptyId: "newsletters-empty",
          carouselId: "newsletters-carousel"
        },
        reports: {
          type: "Report",
          gridId: "public-reports-grid",
          loadingId: "reports-loading",
          errorId: "reports-error",
          emptyId: "reports-empty",
          carouselId: "reports-carousel"
        }
      };

      const loadedPublicationRoutes = new Set();

      function formatPublicationDate(dateValue) {
        if (!dateValue) {
          return "Date not specified";
        }

        const date = new Date(`${dateValue}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
          return dateValue;
        }

        return new Intl.DateTimeFormat("en-GB", {
          day: "numeric",
          month: "long",
          year: "numeric"
        }).format(date);
      }

      function showPublicationState(route, state) {
        const settings = publicationSettings[route];

        if (!settings) return;

        const loading = document.getElementById(settings.loadingId);
        const error = document.getElementById(settings.errorId);
        const empty = document.getElementById(settings.emptyId);
        const carousel = document.getElementById(settings.carouselId);

        if (loading) {
          loading.hidden = state !== "loading";
        }

        if (error) {
          error.hidden = state !== "error";
        }

        if (empty) {
          empty.hidden = state !== "empty";
        }

        if (carousel) {
          carousel.hidden = state !== "success";
        }
      }

      function createPublicationCard(publication) {
        const article = document.createElement("article");
        article.className = "publication-card";

        if (Number(publication.is_featured) === 1) {
          article.classList.add("is-featured");
        }

        article.innerHTML = `
          ${
            publication.cover_image
              ? `
                <div class="publication-card-image-wrap">
                  <img
                    src="${escapeHtml(publication.cover_image)}"
                    alt=""
                    class="publication-card-image"
                    loading="lazy"
                  >
                </div>
              `
              : ""
          }

          <div class="publication-card-content">
            <div class="publication-card-heading">
              <p class="publication-type">
                ${escapeHtml(publication.publication_type)}
              </p>

              ${
                Number(publication.is_featured) === 1
                  ? '<span class="publication-featured-badge">Featured</span>'
                  : ""
              }
            </div>

            <h3>${escapeHtml(publication.title)}</h3>

            <p class="publication-summary">
              ${escapeHtml(publication.summary)}
            </p>

            <div class="publication-meta">
              ${
                publication.author
                  ? `
                    <span>
                      <strong>Author:</strong>
                      ${escapeHtml(publication.author)}
                    </span>
                  `
                  : ""
              }

              <span>
                <strong>Date:</strong>
                ${escapeHtml(
                  formatPublicationDate(publication.publication_date)
                )}
              </span>
            </div>

            <a
              href="publication.php?slug=${encodeURIComponent(
                publication.slug
              )}"
              class="read-more-link"
            >
              Read more →
            </a>
          </div>
        `;

        return article;
      }

      async function loadPublications(route) {
        const settings = publicationSettings[route];

        if (!settings || loadedPublicationRoutes.has(route)) {
          return;
        }

        const grid = document.getElementById(settings.gridId);

        if (!grid) {
          return;
        }

        loadedPublicationRoutes.add(route);
        showPublicationState(route, "loading");

        try {
          const response = await fetch(
            `api/publications.php?type=${encodeURIComponent(settings.type)}`,
            {
              headers: {
                Accept: "application/json"
              }
            }
          );

          if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
          }

          const data = await response.json();

          if (!data.success || !Array.isArray(data.publications)) {
            throw new Error("Invalid publications API response");
          }

          grid.innerHTML = "";

          if (data.publications.length === 0) {
            showPublicationState(route, "empty");
            return;
          }

          data.publications.forEach(function (publication) {
            grid.appendChild(createPublicationCard(publication));
          });

          showPublicationState(route, "success");
        } catch (error) {
          console.error(`Unable to load ${route}:`, error);
          loadedPublicationRoutes.delete(route);
          showPublicationState(route, "error");
        }
      }

      function loadPublicationsForCurrentRoute() {
        const route =
          window.location.hash.replace("#", "") || "blogs";

        if (publicationSettings[route]) {
          loadPublications(route);
        }
      }

      function scrollPublicationCarousel(route, direction) {
        const settings = publicationSettings[route];

        if (!settings) return;

        const grid = document.getElementById(settings.gridId);

        if (!grid) return;

        const card = grid.querySelector(".publication-card");

        const scrollDistance = card
          ? card.getBoundingClientRect().width + 20
          : grid.clientWidth * 0.8;

        grid.scrollBy({
          left: direction * scrollDistance,
          behavior: "smooth"
        });
      }

      document
        .querySelectorAll("[data-publication-previous]")
        .forEach(function (button) {
          button.addEventListener("click", function () {
            scrollPublicationCarousel(
              button.dataset.publicationPrevious,
              -1
            );
          });
        });

      document
        .querySelectorAll("[data-publication-next]")
        .forEach(function (button) {
          button.addEventListener("click", function () {
            scrollPublicationCarousel(
              button.dataset.publicationNext,
              1
            );
          });
        });

      loadPublicationsForCurrentRoute();

      window.addEventListener(
        "hashchange",
        loadPublicationsForCurrentRoute
      );
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
      <section
        id="blogs"
        class="about-route publication-route"
        data-publication-route  
      >
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Blogs</p>

            <p>
              Read articles, reflections and informed perspectives from researchers,
              professionals and contributors on research, mentorship, innovation and
              community development.
            </p>
          </div>

          <div
            id="blogs-loading"
            class="publication-state publication-loading-state"
            role="status"
          >
            <p>Loading blogs...</p>
          </div>

          <div
            id="blogs-error"
            class="publication-state publication-error-state"
            role="alert"
            hidden
          >
            <h3>Unable to load blogs</h3>
            <p>
              We could not retrieve the blogs at this time. Please try again later.
            </p>
          </div>

          <div
            id="blogs-empty"
            class="publication-state publication-empty-state"
            hidden
          >
            <h3>No blogs available</h3>
            <p>There are currently no published blog posts.</p>
          </div>

          <div
            id="blogs-carousel"
            class="publication-carousel"
            hidden
          >
            <button
              type="button"
              class="publication-carousel-button previous"
              data-publication-previous="blogs"
              aria-label="View previous blogs"
            >
              ‹
            </button>

            <div class="publication-viewport">
              <div
                id="public-blogs-grid"
                class="publication-grid"
                aria-live="polite"
              ></div>
            </div>

            <button
              type="button"
              class="publication-carousel-button next"
              data-publication-next="blogs"
              aria-label="View more blogs"
            >
              ›
            </button>
          </div>
        </div>
      </section>


      <!-- =====================================================
          NEWSLETTERS SECTION
          Route: publications.php#newsletters
      ====================================================== -->
      <section
        id="newsletters"
        class="about-route publication-route"
        data-publication-route
        hidden
      >
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Newsletters</p>

            <p>
              Follow updates on our programmes, partnerships, events, opportunities,
              research activities and institutional development.
            </p>
          </div>

          <div
            id="newsletters-loading"
            class="publication-state publication-loading-state"
            role="status"
          >
            <p>Loading newsletters...</p>
          </div>

          <div
            id="newsletters-error"
            class="publication-state publication-error-state"
            role="alert"
            hidden
          >
            <h3>Unable to load newsletters</h3>
            <p>
              We could not retrieve the newsletters at this time. Please try again later.
            </p>
          </div>

          <div
            id="newsletters-empty"
            class="publication-state publication-empty-state"
            hidden
          >
            <h3>No newsletters available</h3>
            <p>There are currently no published newsletters.</p>
          </div>

          <div
            id="newsletters-carousel"
            class="publication-carousel"
            hidden
          >
            <button
              type="button"
              class="publication-carousel-button previous"
              data-publication-previous="newsletters"
              aria-label="View previous newsletters"
            >
              ‹
            </button>

            <div class="publication-viewport">
              <div
                id="public-newsletters-grid"
                class="publication-grid"
                aria-live="polite"
              ></div>
            </div>

            <button
              type="button"
              class="publication-carousel-button next"
              data-publication-next="newsletters"
              aria-label="View more newsletters"
            >
              ›
            </button>
          </div>
        </div>
      </section>


      <!-- =====================================================
          REPORTS SECTION
          Route: publications.php#reports
      ====================================================== -->
      <section
        id="reports"
        class="about-route publication-route"
        data-publication-route
        hidden
      >
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Reports</p>

            <p>
              Access research reports, annual reports, financial reports, policy
              briefs and other institutional publications from EA Research Group.
            </p>
          </div>

          <div
            id="reports-loading"
            class="publication-state publication-loading-state"
            role="status"
          >
            <p>Loading reports...</p>
          </div>

          <div
            id="reports-error"
            class="publication-state publication-error-state"
            role="alert"
            hidden
          >
            <h3>Unable to load reports</h3>
            <p>
              We could not retrieve the reports at this time. Please try again later.
            </p>
          </div>

          <div
            id="reports-empty"
            class="publication-state publication-empty-state"
            hidden
          >
            <h3>No reports available</h3>
            <p>There are currently no published reports.</p>
          </div>

          <div
            id="reports-carousel"
            class="publication-carousel"
            hidden
          >
            <button
              type="button"
              class="publication-carousel-button previous"
              data-publication-previous="reports"
              aria-label="View previous reports"
            >
              ‹
            </button>

            <div class="publication-viewport">
              <div
                id="public-reports-grid"
                class="publication-grid"
                aria-live="polite"
              ></div>
            </div>

            <button
              type="button"
              class="publication-carousel-button next"
              data-publication-next="reports"
              aria-label="View more reports"
            >
              ›
            </button>
          </div>
        </div>
      </section>


      <!-- =====================================================
          SCHOLARSHIP & OPPORTUNITIES SECTION
          Route: publications.php#scholarship-opportunities
      ====================================================== -->
      <section
        id="scholarship-opportunities"
        class="about-route publication-route"
        data-publication-route
        hidden
      >
        <div class="container">
          <div class="section-heading wide">
            <p class="eyebrow">Scholarship & Opportunities</p>

            <p>
              Explore widely available scholarships, fellowships, internships, grants,
              conferences, trainings and other academic or professional opportunities
              for students, graduates and early-career researchers.
            </p>
          </div>

          <div
            id="opportunities-loading"
            class="opportunity-state opportunity-loading-state"
            role="status"
          >
            <p>Loading opportunities...</p>
          </div>

          <div
            id="opportunities-error"
            class="opportunity-state opportunity-error-state"
            role="alert"
            hidden
          >
            <h3>Unable to load opportunities</h3>
            <p>
              We could not retrieve the opportunities at this time. Please try again later.
            </p>
          </div>

          <div
            id="opportunities-empty"
            class="opportunity-state opportunity-empty-state"
            hidden
          >
            <h3>No opportunities available</h3>
            <p>
              There are currently no published opportunities. Please check again soon.
            </p>
          </div>

          <div
            id="opportunities-carousel"
            class="opportunity-carousel"
            hidden
          >
            <button
              type="button"
              class="opportunity-carousel-button previous"
              id="opportunities-previous"
              aria-label="View previous opportunities"
            >
              ‹
            </button>

            <div class="opportunity-viewport">
              <div
                id="public-opportunities-grid"
                class="opportunity-grid"
                aria-live="polite"
              ></div>
            </div>

            <button
              type="button"
              class="opportunity-carousel-button next"
              id="opportunities-next"
              aria-label="View more opportunities"
            >
              ›
            </button>
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
