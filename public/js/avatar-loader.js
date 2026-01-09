function hydrateAvatars(root = document) {
  const avatars = root.querySelectorAll("img[data-avatar]");

  avatars.forEach((el) => {
    const real = el.dataset.avatar;
    if (!real || real.trim() === "") return;
    if (el.dataset.hydrated === "1") return;
    el.dataset.hydrated = "1";

    const img = new Image();
    img.src = real;

    img.onload = () => {
      if (document.body.contains(el)) {
        el.src = real;
      }
    };
  });
}

document.addEventListener("DOMContentLoaded", () => hydrateAvatars());
window.hydrateAvatars = hydrateAvatars;
