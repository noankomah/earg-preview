/**
 * Shared behaviour for the standalone About pages (views/About/*).
 *
 * The navigation bar and footer are shared includes, so the small
 * interactions they need live here instead of being repeated inline on
 * every About page.
 */
document.addEventListener("DOMContentLoaded", function () {
  /* Mobile menu: tapping an "About Us / Programs / Publications" button
     opens that dropdown and closes any other open dropdown. On desktop the
     dropdowns open on hover, so this is only wired up on small screens. */
  var dropdowns = document.querySelectorAll(".nav-dropdown");

  dropdowns.forEach(function (dropdown) {
    var parent = dropdown.querySelector(".nav-parent");

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

  /* Footer copyright year. */
  var year = document.getElementById("year");

  if (year) {
    year.textContent = new Date().getFullYear();
  }

  /* Keep the sticky-header offset in sync with the real header height so
     in-page anchors are not hidden behind the header. */
  function setHeaderOffset() {
    var header = document.querySelector(".site-header");
    var headerHeight = header ? header.offsetHeight : 78;

    document.documentElement.style.setProperty(
      "--header-offset",
      headerHeight + 16 + "px"
    );
  }

  setHeaderOffset();
  window.addEventListener("load", setHeaderOffset);
  window.addEventListener("resize", setHeaderOffset);

  /* Contact page only: show a notice after contact.php redirects back with
     ?status=sent or ?status=error. The notice element is absent elsewhere,
     so this block simply does nothing on the other About pages. */
  var contactStatus = document.getElementById("contactStatus");

  if (contactStatus) {
    var status = new URLSearchParams(window.location.search).get("status");

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
