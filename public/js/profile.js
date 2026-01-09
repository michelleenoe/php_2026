document.addEventListener("DOMContentLoaded", () => {
  function validateImage(file) {
    const allowed = ["image/jpeg", "image/png", "image/webp"];
    const max = 3 * 1024 * 1024;
    if (!allowed.includes(file.type)) {
      showToast("File type not allowed. Use JPG, PNG or WEBP.");
      return false;
    }
    if (file.size > max) {
      showToast("File is too large (max 3 MB).");
      return false;
    }
    return true;
  }

  const coverInput = document.querySelector('.cover-upload-form input[type="file"]');
  const coverBtn = document.querySelector(".cover-upload-btn");
  const coverIcon = document.querySelector(".cover-upload-btn i");
  const coverImg = document.getElementById("coverPreview");

  if (coverInput && coverBtn && coverIcon && coverImg) {
    coverInput.addEventListener("change", () => {
      if (!coverInput.files.length) return;
      const file = coverInput.files[0];
      if (!validateImage(file)) {
        coverInput.value = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        coverImg.src = e.target.result;
      };
      reader.readAsDataURL(file);

      coverIcon.classList.remove("fa-camera");
      coverIcon.classList.add("fa-check");

      coverBtn.addEventListener(
        "click",
        function handler(e) {
          e.preventDefault();
          coverInput.form.submit();
        },
        { once: true }
      );
    });
  }

  const avatarInput = document.querySelector('.avatar-edit-btn input[type="file"]');
  const avatarBtn = document.querySelector(".avatar-edit-btn");
  const avatarIcon = document.querySelector(".avatar-edit-btn i");

  if (avatarInput && avatarBtn && avatarIcon) {
    const avatarImg = avatarInput.closest(".avatar-upload-form")
      .querySelector(".profile-avatar");

    avatarInput.addEventListener("change", () => {
      if (!avatarInput.files.length) return;
      const file = avatarInput.files[0];
      if (!validateImage(file)) {
        avatarInput.value = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        avatarImg.src = e.target.result;
      };
      reader.readAsDataURL(file);

      avatarIcon.classList.remove("fa-camera");
      avatarIcon.classList.add("fa-check");

      showToast("Profile picture ready. Click the icon to save.");

      avatarBtn.addEventListener(
        "click",
        function handler(e) {
          e.preventDefault();
          avatarInput.form.submit();
        },
        { once: true }
      );
    });
  }

  const filterWrappers = Array.from(document.querySelectorAll(".profile-filter"));
  filterWrappers.forEach((wrapper) => {
    wrapper.classList.remove("open");
    const toggle = wrapper.querySelector(".filter-toggle");
    if (!toggle) return;
    toggle.setAttribute("aria-expanded", "false");

    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      filterWrappers.forEach((w) => {
        if (w !== wrapper) {
          w.classList.remove("open");
          const t = w.querySelector(".filter-toggle");
          if (t) t.setAttribute("aria-expanded", "false");
        }
      });
      const isOpen = wrapper.classList.toggle("open");
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  });

  document.addEventListener("click", (e) => {
    filterWrappers.forEach((wrapper) => {
      if (!wrapper.contains(e.target)) {
        wrapper.classList.remove("open");
        const toggle = wrapper.querySelector(".filter-toggle");
        if (toggle) toggle.setAttribute("aria-expanded", "false");
      }
    });
  });
});
