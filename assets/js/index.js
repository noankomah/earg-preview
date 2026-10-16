document.addEventListener("DOMContentLoaded", function () {
  const year = document.getElementById("year");
  if (year) {
    year.textContent = new Date().getFullYear();
  }

  const slider = document.getElementById("heroSlider");
  const dotsContainer = document.getElementById("sliderDots");
  const nextBtn = document.getElementById("nextSlide");
  const prevBtn = document.getElementById("prevSlide");

  let slides = [];
  let dots = [];
  let currentSlide = 0;
  let slideInterval;

  // The hero slides and their dots are rendered by index.php from the
  // hero_slides table, which is managed in the admin area (admin/hero/).
  // This script only wires up the rotation and the controls.
  slides = slider ? slider.querySelectorAll(".slide") : [];
  dots = dotsContainer ? dotsContainer.querySelectorAll(".dot") : [];

  // Attach the click handler to each dot rendered by the server.
  dots.forEach(function (dot, index) {
    dot.addEventListener("click", function () {
      showSlide(index);
      resetAutoSlide();
    });
  });

  if (slides.length > 1) {
    startAutoSlide();
  }

  function showSlide(index) {
    if (!slides.length) return;

    slides.forEach(function (slide, i) {
      slide.classList.toggle("is-active", i === index);
    });

    dots.forEach(function (dot, i) {
      dot.classList.toggle("is-active", i === index);
    });

    currentSlide = index;
  }

  function nextSlide() {
    if (!slides.length) return;
    const next = (currentSlide + 1) % slides.length;
    showSlide(next);
  }

  function prevSlide() {
    if (!slides.length) return;
    const previous = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(previous);
  }

  function startAutoSlide() {
    slideInterval = setInterval(nextSlide, 5000);
  }

  function resetAutoSlide() {
    clearInterval(slideInterval);
    startAutoSlide();
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", function () {
      nextSlide();
      resetAutoSlide();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", function () {
      prevSlide();
      resetAutoSlide();
    });
  }

  const newsletterForm = document.querySelector(".newsletter-form");
  const newsletterMessage = document.getElementById("newsletterMessage");

  if (newsletterForm) {
    // Show a result message in the newsletter box.
    function showNewsletterMessage(text, isError) {
      if (!newsletterMessage) return;

      newsletterMessage.textContent = text;
      newsletterMessage.classList.toggle("is-error", Boolean(isError));
      newsletterMessage.classList.toggle("is-success", !isError);
      newsletterMessage.hidden = false;
    }

    newsletterForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const email = newsletterForm.querySelector('input[name="email"]');

      if (!email || !email.value) return;

      // Send the email address to newsletter.php which stores it in the DB.
      fetch("newsletter.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "email=" + encodeURIComponent(email.value),
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.success) {
            newsletterForm.reset();
          }

          showNewsletterMessage(data.message || "Thank you for subscribing!", !data.success);
        })
        .catch(function () {
          showNewsletterMessage("Unable to subscribe right now. Please try again later.", true);
        });
    });
  }
});