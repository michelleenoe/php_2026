export function searchOverlay() {

  const overlay = document.querySelector(".search-overlay");
  if (!overlay) return;

  const overlayBox = overlay.querySelector(".search-overlay-box");
  const overlayForm = document.querySelector("#searchOverlayForm");
  const overlayInput = document.querySelector("#searchOverlayInput");
  const overlayResults = document.querySelector("#searchOverlayResults");
  const overlayClose = document.querySelector(".search-overlay-close");

  const homeInput = document.querySelector("#home-search-input");
  const homeForm = document.querySelector("#home-search-form");
  const exploreBtn = document.querySelector(".open-search");
  const profileInput = document.querySelector("#profile-search-input");

  let debounceTimer = null;

  function open(initialValue = "") {
    overlay.classList.add("search-overlay-open");
    overlay.setAttribute("aria-hidden", "false");
    overlayInput.value = initialValue;
    overlayInput.focus();
    overlayResults.innerHTML = "";
  }

  function close() {
    overlay.classList.remove("search-overlay-open");
    overlay.setAttribute("aria-hidden", "true");
  }

  function highlight(text, query) {
    if (!query) return text;
    const regex = new RegExp(`(${query})`, "gi");
    return text.replace(regex, `<mark>$1</mark>`);
  }

  function performSearch(query) {
    fetch("/bridge-search", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "query=" + encodeURIComponent(query),
    })
      .then((r) => r.text())
      .then((html) => {
        overlayResults.innerHTML = html;

        overlayResults.querySelectorAll("[data-search-text]").forEach((el) => {
          const original = el.dataset.searchText;
          el.innerHTML = highlight(original, query);
        });
      });
  }

  function liveSearch(query) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      if (!query) {
        overlayResults.innerHTML = "";
        return;
      }
      performSearch(query);
    }, 250);
  }

  if (homeInput) {
    homeInput.addEventListener("focus", () => open(homeInput.value));
  }

  if (homeForm) {
    homeForm.addEventListener("submit", (e) => {
      e.preventDefault();
      open(homeInput.value);
    });
  }

  if (exploreBtn) {
    exploreBtn.addEventListener("click", (e) => {
      e.preventDefault();
      open("");
    });
  }
  if (profileInput) {
    profileInput.addEventListener("focus", () => open(profileInput.value));
  }
  overlayInput.addEventListener("input", () => {
    const query = overlayInput.value.trim();
    liveSearch(query);
  });

  if (overlayForm) {
    overlayForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const q = overlayInput.value.trim();
      if (q) performSearch(q);
    });
  }

  if (overlayClose) overlayClose.addEventListener("click", close);

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) close();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });
}