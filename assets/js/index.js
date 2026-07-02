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

  fetch("assets/images/hero_images/images.json")
    .then(function (response) {
      return response.json();
    })
    .then(function (images) {
      if (!images || images.length === 0) {
        slider.innerHTML = '<div class="slide is-active" style="background-image: linear-gradient(90deg, rgba(15,47,36,0.88), rgba(15,47,36,0.45));"></div>';
        return;
      }

      images.forEach(function (imagePath, index) {
        const slide = document.createElement("div");
        slide.className = "slide";
        slide.style.backgroundImage = `url("${imagePath}")`;

        if (index === 0) {
          slide.classList.add("is-active");
        }

        slider.appendChild(slide);

        const dot = document.createElement("button");
        dot.className = "dot";
        dot.setAttribute("aria-label", `Go to slide ${index + 1}`);
        dot.addEventListener("click", function () {
          showSlide(index);
          resetAutoSlide();
        });

        if (index === 0) {
          dot.classList.add("is-active");
        }

        dotsContainer.appendChild(dot);
      });

      slides = document.querySelectorAll(".slide");
      dots = document.querySelectorAll(".dot");

      startAutoSlide();
    })
    .catch(function () {
      slider.innerHTML = '<div class="slide is-active" style="background-image: linear-gradient(90deg, rgba(15,47,36,0.88), rgba(15,47,36,0.45));"></div>';
    });

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
    slideInterval = setInterval(nextSlide, 30000);
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
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (event) {
      event.preventDefault();
      alert("Thank you for subscribing. Newsletter integration will be connected later.");
      newsletterForm.reset();
    });
  }
});