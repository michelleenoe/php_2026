function initDeleteConfirm() {
  const dialog = document.getElementById("updatePostDialog");
  if (!dialog) return;

  const deleteBtn = dialog.querySelector("#deletePostBtn");
  const confirmBox = dialog.querySelector("#deleteConfirm");
  const cancelBtn = dialog.querySelector("#deleteCancel");
  const confirmYes = dialog.querySelector("#deleteConfirmYes");
  const postPkInput = dialog.querySelector("#postPkInput");

  if (!deleteBtn || !confirmBox || !cancelBtn || !confirmYes || !postPkInput) {
    return;
  }

  deleteBtn.addEventListener("click", () => {
    confirmBox.classList.add("delete-confirm--visible");
    deleteBtn.style.display = "none";
  });

  cancelBtn.addEventListener("click", () => {
    confirmBox.classList.remove("delete-confirm--visible");
    deleteBtn.style.display = "";
  });

  confirmYes.addEventListener("click", () => {
    const pk = postPkInput.value;
    if (!pk) return;

    const redirect = window.location.pathname + window.location.search;
    window.location.href = "/api-delete-post?post_pk=" + encodeURIComponent(pk) + "&redirect_to=" + encodeURIComponent(redirect);
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initDeleteConfirm);
} else {
  initDeleteConfirm();
}

document.addEventListener("click", async function (e) {
  try {
    const btn = e.target.closest && e.target.closest(".x-dialog__btn_del");
    if (!btn) return;
    const dialog = btn.closest("#updateProfileDialog");
    if (!dialog) return;

    e.preventDefault();
    const container = dialog.querySelector(".x-dialog__form") || dialog;

    if (typeof showDeleteConfirmInline === "function") {
      const ok = await showDeleteConfirmInline(container, "Are you sure you want to delete your profile? This action cannot be undone.");
      if (!ok) return;
    } else {
      const ok = window.confirm("Are you sure you want to delete your profile? This action cannot be undone.");
      if (!ok) return;
    }

    window.location.href = "/api-delete-profile";
  } catch (err) {}
});

document.addEventListener("submit", async function (e) {
  const form = e.target.closest && e.target.closest("#updateProfileDialog .x-dialog__form");
  if (!form) return;
  e.preventDefault();
  try {
    const fd = new FormData(form);
    const res = await fetch(form.getAttribute("action") || "/api-update-profile", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
      body: fd,
    });

    let data = null;
    try {
      data = await res.json();
    } catch (e) {
      showToast("Could not update profile", "error");
      return;
    }

    if (!res.ok || !data || data.success !== true) {
      if (data && data.error_code === "no_change") {
        showToast(data.message || "Please change something before updating", "error");
        return;
      }

      if (data && data.error_code === "email_taken") {
        showToast(data.message || "Email is already taken", "error");
        const emailInput = form.querySelector('input[name="user_email"]');
        if (emailInput) emailInput.focus();
        return;
      }

      if (data && data.error_code === "username_taken") {
        showToast(data.message || "Username is already taken", "error");
        const usernameInput = form.querySelector('input[name="user_username"]');
        if (usernameInput) usernameInput.focus();
        return;
      }

      showToast((data && (data.message || data.error)) || "Could not update profile", "error");
      return;
    }

    showToast(data.message || "Profile updated", "ok");
    setTimeout(() => location.reload(), 600);
    return;
  } catch (err) {
    showToast("Network error while updating profile", "error");
  }
});

document.addEventListener("change", function (e) {
  const fileInput = e.target;
  if (fileInput && fileInput.id === "postImageInput") {
    const file = fileInput.files[0];
    const previewContainer = document.getElementById("postImagePreviewContainer");
    const previewImg = document.getElementById("postImagePreview");
    const maxFileSize = 3 * 1024 * 1024; // 3 MB

    if (file) {
      if (file.size > maxFileSize) {
        showToast("Image too large - max size 3 MB", "error");
        fileInput.value = "";
        previewContainer.style.display = "none";
        return;
      }

      if (!file.type.startsWith("image/")) {
        showToast("Please select a valid image file", "error");
        fileInput.value = "";
        previewContainer.style.display = "none";
        return;
      }

      if (previewContainer && previewImg) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImg.src = e.target.result;
          previewContainer.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    }
  }
});

document.addEventListener("click", function (e) {
  if (e.target && e.target.id === "removePostImage") {
    const fileInput = document.getElementById("postImageInput");
    const previewContainer = document.getElementById("postImagePreviewContainer");
    const previewImg = document.getElementById("postImagePreview");

    if (fileInput) fileInput.value = "";
    if (previewImg) previewImg.src = "";
    if (previewContainer) previewContainer.style.display = "none";
  }
});
