function setupAutoReload(options = {}) {
  const { selectors = [], selector = null, delay = 200, event = "click" } = options;

  const targetSelectors = selector ? [selector] : selectors;
  if (targetSelectors.length === 0) {
    console.warn("setupAutoReload: no selectors provided");
    return;
  }

  document.addEventListener(event, function (e) {
    const targets = targetSelectors.map((s) => e.target.closest(s)).filter(Boolean);

    if (targets.length > 0) {

      targets[0].classList.add("loading");
      if (targets[0].tagName === "BUTTON") {
        targets[0].disabled = true;
      }

      setTimeout(function () {
        location.reload();
      }, delay);
    }
  });
}

window.setupAutoReload = setupAutoReload;
