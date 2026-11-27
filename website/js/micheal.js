(() => {
  "use strict";

  const menuToggle = document.querySelector(".menu-toggle");
  const navigation = document.querySelector(".primary-navigation");

  if (menuToggle && navigation) {
    const closeMenu = () => {
      menuToggle.setAttribute("aria-expanded", "false");
      menuToggle.setAttribute("aria-label", "Open navigation menu");
      navigation.classList.remove("is-open");
    };

    menuToggle.addEventListener("click", () => {
      const isExpanded = menuToggle.getAttribute("aria-expanded") === "true";
      menuToggle.setAttribute("aria-expanded", String(!isExpanded));
      menuToggle.setAttribute("aria-label", isExpanded ? "Open navigation menu" : "Close navigation menu");
      navigation.classList.toggle("is-open", !isExpanded);
    });

    navigation.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", closeMenu);
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") closeMenu();
    });
  }

  const year = document.querySelector("#current-year");
  if (year) year.textContent = String(new Date().getFullYear());

  const revealItems = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealItems.forEach((item) => revealObserver.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add("is-visible"));
  }

  const filterButtons = document.querySelectorAll(".filter-button");
  const galleryItems = Array.from(document.querySelectorAll(".gallery-item"));
  const galleryCount = document.querySelector(".gallery-count");

  const applyGalleryFilter = (filter) => {
    let visibleCount = 0;
    galleryItems.forEach((item) => {
      const visible = filter === "all" || item.dataset.category === filter;
      item.hidden = !visible;
      if (visible) visibleCount += 1;
    });
    if (galleryCount) galleryCount.textContent = `${visibleCount} photograph${visibleCount === 1 ? "" : "s"}`;
  };

  filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      filterButtons.forEach((filterButton) => {
        const selected = filterButton === button;
        filterButton.classList.toggle("is-active", selected);
        filterButton.setAttribute("aria-pressed", String(selected));
      });
      applyGalleryFilter(button.dataset.filter || "all");
    });
  });
  applyGalleryFilter("all");

  const lightbox = document.querySelector("#lightbox");
  if (lightbox instanceof HTMLDialogElement) {
    const lightboxImage = lightbox.querySelector("figure img");
    const lightboxCaption = lightbox.querySelector("figcaption");
    const lightboxIndex = lightbox.querySelector(".lightbox-index");
    let visibleGalleryItems = galleryItems;
    let activeGalleryIndex = 0;

    const getVisibleItems = () => galleryItems.filter((item) => !item.hidden);
    const showGalleryItem = (index) => {
      visibleGalleryItems = getVisibleItems();
      activeGalleryIndex = (index + visibleGalleryItems.length) % visibleGalleryItems.length;
      const item = visibleGalleryItems[activeGalleryIndex];
      const image = item.querySelector("img");
      if (!image || !lightboxImage || !lightboxCaption || !lightboxIndex) return;
      lightboxImage.src = item.dataset.full || image.src;
      lightboxImage.alt = image.alt;
      lightboxCaption.textContent = item.dataset.caption || image.alt;
      lightboxIndex.textContent = `${activeGalleryIndex + 1} / ${visibleGalleryItems.length}`;
    };

    galleryItems.forEach((item) => {
      item.addEventListener("click", () => {
        visibleGalleryItems = getVisibleItems();
        const index = visibleGalleryItems.indexOf(item);
        if (index < 0) return;
        showGalleryItem(index);
        lightbox.showModal();
      });
    });

    lightbox.querySelector(".lightbox-close")?.addEventListener("click", () => lightbox.close());
    lightbox.querySelector(".lightbox-prev")?.addEventListener("click", () => showGalleryItem(activeGalleryIndex - 1));
    lightbox.querySelector(".lightbox-next")?.addEventListener("click", () => showGalleryItem(activeGalleryIndex + 1));

    lightbox.addEventListener("click", (event) => {
      if (event.target === lightbox) lightbox.close();
    });

    lightbox.addEventListener("keydown", (event) => {
      if (event.key === "ArrowLeft") showGalleryItem(activeGalleryIndex - 1);
      if (event.key === "ArrowRight") showGalleryItem(activeGalleryIndex + 1);
    });
  }

  const newsSearch = document.querySelector("#news-search");
  const newsCards = Array.from(document.querySelectorAll(".news-card"));
  const newsEmpty = document.querySelector("#news-empty");
  if (newsSearch) {
    newsSearch.addEventListener("input", () => {
      const query = newsSearch.value.trim().toLocaleLowerCase();
      let matches = 0;
      newsCards.forEach((card) => {
        const searchableText = `${card.dataset.search || ""} ${card.textContent || ""}`.toLocaleLowerCase();
        const visible = searchableText.includes(query);
        card.hidden = !visible;
        if (visible) matches += 1;
      });
      if (newsEmpty) newsEmpty.hidden = matches !== 0;
    });
  }

  document.querySelectorAll(".enquiry-form").forEach((form) => {
    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!(form instanceof HTMLFormElement) || !form.reportValidity()) return;

      const submitButton = form.querySelector('button[type="submit"]');
      const status = form.querySelector(".form-status");
      if (!(submitButton instanceof HTMLButtonElement) || !status) return;

      submitButton.disabled = true;
      status.classList.remove("is-error");
      status.textContent = "Sending your enquiry…";

      try {
        const response = await fetch(form.action, {
          method: "POST",
          body: new FormData(form),
          headers: { Accept: "application/json" }
        });
        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || "Your enquiry could not be sent. Please check the form and try again.");
        }

        status.textContent = result.message || "Thank you. Your enquiry has been received.";
        form.reset();
      } catch (error) {
        status.classList.add("is-error");
        status.textContent = error instanceof TypeError
          ? "The enquiry service is unavailable. Please open the website through XAMPP or try again later."
          : error.message;
      } finally {
        submitButton.disabled = false;
      }
    });
  });

})();
