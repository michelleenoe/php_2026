let openEditCommentPk = null;
let openDeleteCommentPk = null;

function closeAllEditAndDelete() {
  document.querySelectorAll(".edit-comment-form").forEach((f) => {
    f.style.display = "none";
  });
  document.querySelectorAll(".comment-text").forEach((t) => {
    t.style.display = "block";
  });

  document
    .querySelectorAll(".delete-confirm-comment")
    .forEach((dc) => dc.remove());

  openEditCommentPk = null;
  openDeleteCommentPk = null;

  enableCommentInput(true);
}

function enableCommentInput(enable) {
  document.querySelectorAll(".comment-form textarea").forEach((t) => {
    t.disabled = !enable;
  });
  document.querySelectorAll(".comment-form_btn").forEach((b) => {
    b.disabled = !enable;
  });
}

function showDeleteConfirmInline(containerElement, message) {
  return new Promise((resolve) => {
    const tpl = document.getElementById("deleteConfirmTemplate");
    const dialog = tpl.content
      .cloneNode(true)
      .querySelector(".delete-confirm-comment");

    dialog.querySelector(".delete-confirm__message").textContent = message;

    const btnCancel = dialog.querySelector(".delete-confirm__btn--secondary");
    const btnOk = dialog.querySelector(".delete-confirm__btn--danger");

    function cleanup(result) {
      btnCancel.removeEventListener("click", onCancel);
      btnOk.removeEventListener("click", onOk);
      dialog.remove();
      resolve(result);
    }

    function onCancel(e) {
      e.preventDefault();
      cleanup(false);
    }

    function onOk(e) {
      e.preventDefault();
      cleanup(true);
    }

    btnCancel.addEventListener("click", onCancel);
    btnOk.addEventListener("click", onOk);

    containerElement.appendChild(dialog);
  });
}

async function sendForm(url, payload) {
  const body = new URLSearchParams(payload).toString();

  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body,
  });

  let data = {};
  try {
    data = await response.json();
  } catch (_) {}

  return { response, data };
}

async function loadComments(postPk, userPk, contextElement) {
  const res = await fetch(
    `/api-get-comments?post_pk=${encodeURIComponent(postPk)}`
  );
  if (!res.ok) return;

  const comments = await res.json();
  const container =
    contextElement?.querySelector(".comments-container") ||
    document.getElementById(`commentsContainer_${postPk}`);
  if (!container) return;

  container.innerHTML = "";
  comments.forEach((comment) => {
    const node = createCommentElement(comment, userPk);
    container.appendChild(node);
  });

  const btn =
    contextElement?.querySelector(
      `.comment-btn[data-comment-target="${postPk}"]`
    ) || document.querySelector(`.comment-btn[data-comment-target="${postPk}"]`);
  if (btn) {
    const countSpan = btn.querySelector(".comment-count");
    if (countSpan) {
      countSpan.textContent = comments.length;
    }
  }
}

function createCommentElement(comment, userPk) {
  const tpl = document.getElementById("commentTemplate");
  const node = tpl.content.cloneNode(true);
  const wrapper = node.querySelector(".comment");

  wrapper.dataset.commentPk = comment.comment_pk;

  const profileUrl = `/user?user_pk=${encodeURIComponent(
    comment.comment_user_fk
  )}`;

  node.querySelectorAll(".comment-author-link").forEach((a) => {
    a.href = profileUrl;
  });

  const avatarEl = node.querySelector(".comment-avatar");
  let avatarPath = "/public/img/avatar.jpg";

  if (comment.user_avatar) {
    if (comment.user_avatar.startsWith("http")) {
      avatarPath = comment.user_avatar;
    } else if (comment.user_avatar.startsWith("/")) {
      avatarPath = comment.user_avatar;           
    } else {
      avatarPath = `/uploads/${comment.user_avatar}`;
    }
  }

  avatarEl.src = avatarPath;

  node.querySelector(".name").textContent = comment.user_full_name || "Unknown";
  node.querySelector(".handle").textContent =
    "@" + (comment.user_username || "");

  const dateStr = comment.comment_created_at
    ? new Date(comment.comment_created_at).toLocaleDateString("da-DK", {
        day: "numeric",
        month: "short",
      })
    : "";

  node.querySelector(".time").textContent = dateStr;
  node.querySelector(".edited").textContent = comment.updated_at
    ? "Edited"
    : "";

  node.querySelector(".comment-text").textContent =
    comment.comment_message || "";

  const textarea = node.querySelector(".edit-comment-textarea");
  textarea.value = comment.comment_message || "";
  textarea.dataset.original = comment.comment_message || "";
  textarea.rows = 1;

  const editBtn = node.querySelector(".edit-comment-btn");
  const deleteBtn = node.querySelector(".delete-comment-btn");
  const form = node.querySelector(".edit-comment-form");

  editBtn.dataset.commentPk = comment.comment_pk;
  deleteBtn.dataset.commentPk = comment.comment_pk;
  form.dataset.commentPk = comment.comment_pk;

  if (comment.comment_user_fk != userPk) {
    node.querySelector(".comment-actions").remove();
  }

  return wrapper;
}

function bindCommentButtons(root = document) {
  (root || document).querySelectorAll(".comment-btn").forEach((btn) => {
    if (btn.dataset.commentBound) return;
    btn.dataset.commentBound = "1";

    btn.addEventListener("click", () => {
      const postPk = btn.getAttribute("data-post-pk");
      const originalPostPk = btn.getAttribute("data-original-post-pk") || postPk; // Use original post PK if available
      const commentTarget =
        btn.getAttribute("data-comment-target") || originalPostPk; // Use original post for comments too
      const userPk = btn.getAttribute("data-user-pk");
      const postElement = btn.closest(".post");
      const dialog =
        postElement?.querySelector(".comment-dialog") ||
        document.getElementById(`commentDialog_${commentTarget}`);
      if (!dialog) return;

      dialog.style.display = dialog.style.display === "block" ? "none" : "block";

      if (dialog.style.display === "block") {
        loadComments(commentTarget, userPk, postElement).catch(console.error);
      }
    });
  });
}

window.bindCommentButtons = bindCommentButtons;
bindCommentButtons();

document.addEventListener("click", (e) => {
  const btn = e.target.closest(".edit-comment-btn");
  if (!btn) return;

  const commentPk = btn.dataset.commentPk;
  const commentDiv = document.querySelector(
    `.comment[data-comment-pk="${commentPk}"]`
  );
  if (!commentDiv) return;

  if (openDeleteCommentPk && openDeleteCommentPk !== commentPk) {
    closeAllEditAndDelete();
  }

  const form = commentDiv.querySelector(".edit-comment-form");
  const text = commentDiv.querySelector(".comment-text");

  if (form.style.display === "block") {
    form.style.display = "none";
    text.style.display = "block";
    openEditCommentPk = null;
    enableCommentInput(true);
    return;
  }

  closeAllEditAndDelete();

  form.style.display = "block";
  text.style.display = "none";
  openEditCommentPk = commentPk;

  enableCommentInput(false);
});

document.addEventListener("submit", async (e) => {
  if (!e.target.classList.contains("edit-comment-form")) return;
  e.preventDefault();

  const form = e.target;
  const commentPk = form.dataset.commentPk;
  const ta = form.querySelector("textarea");
  const message = ta.value;
  const original =
    ta.dataset.original !== undefined
      ? ta.dataset.original
      : ta.defaultValue || "";

  if ((message || "").trim() === (original || "").trim()) {
    showToast("Please change something first", "error");
    return;
  }

  try {
    const { response, data } = await sendForm("/api-update-comment", {
      comment_pk: commentPk,
      comment_message: message,
    });

    if (!response.ok || data.success === false) {
      const msg =
        (data && (data.error || data.message)) ||
        "Something went wrong while updating the comment";
      showToast(msg, "error");
      return;
    }

    const commentDiv = form.closest(".comment");
    if (!commentDiv) return;

    const textEl = commentDiv.querySelector(".comment-text");
    textEl.textContent = message;
    textEl.style.display = "block";
    form.style.display = "none";

    const handleEl = commentDiv.querySelector(".handle");
    if (handleEl && !handleEl.textContent.includes("Edited")) {
      handleEl.textContent = handleEl.textContent.trim() + " · Edited";
    }

    showToast((data && data.message) || "Comment updated", "ok");
  } catch (err) {
    console.error("Error updating comment:", err);
    showToast("Something went wrong while updating the comment", "error");
  }
});

document.addEventListener("click", (e) => {
  if (!e.target.closest(".cancel-edit-btn")) return;

  const commentDiv = e.target.closest(".comment");
  if (!commentDiv) return;

  commentDiv.querySelector(".comment-text").style.display = "block";
  commentDiv.querySelector(".edit-comment-form").style.display = "none";

  openEditCommentPk = null;
  enableCommentInput(true);
});

document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".delete-comment-btn");
  if (!btn) return;

  const commentDiv = btn.closest(".comment");
  const commentPk = commentDiv.dataset.commentPk;
  const commentsContainer = commentDiv.closest(".comments-container");
  const postElement = commentDiv.closest(".post");
  const postPk =
    commentsContainer?.dataset.commentTarget ||
    commentsContainer?.id?.split("_")[1] ||
    postElement?.dataset.commentTarget ||
    postElement?.dataset.postPk;

  if (openEditCommentPk && openEditCommentPk !== commentPk) {
    closeAllEditAndDelete();
  }

  closeAllEditAndDelete();

  openDeleteCommentPk = commentPk;
  enableCommentInput(false);

  e.stopPropagation();
  e.preventDefault();

  const ok = await showDeleteConfirmInline(commentDiv, "Are you sure?");
  openDeleteCommentPk = null;

  enableCommentInput(true);

  if (!ok) return;

  try {
    const { response, data } = await sendForm("/api-delete-comment", {
      comment_pk: commentPk,
    });

    if (!response.ok || data.success === false) {
      const msg =
        (data && (data.error || data.message)) ||
        "Noget gik galt ved sletning af kommentaren";
      showToast(msg, "error");
      return;
    }

    commentDiv.remove();
    const commentBtn =
      postElement?.querySelector(
        `.comment-btn[data-comment-target="${postPk}"]`
      ) ||
      document.querySelector(`.comment-btn[data-comment-target="${postPk}"]`);
    const userPk = commentBtn ? commentBtn.getAttribute("data-user-pk") : null;
    const countSpan = commentBtn
      ? commentBtn.querySelector(".comment-count")
      : null;
    if (countSpan) {
      const current = Number(countSpan.textContent) || 0;
      countSpan.textContent = current > 0 ? current - 1 : 0;
    }

    showToast((data && data.message) || "Comment deleted", "ok");
  } catch (err) {
    console.error("Error deleting comment:", err);
    showToast("Something went wrong while deleting the comment", "error");
  }
});

document.addEventListener("submit", async (e) => {
  if (!e.target.classList.contains("comment-form")) return;
  e.preventDefault();

  const form = e.target;
  const postPk = form.getAttribute("data-post-pk");
  const message = form.querySelector("textarea").value;
  const postElement = form.closest(".post");

  try {
    const { response, data } = await sendForm("/api-create-comment", {
      post_pk: postPk,
      comment_message: message,
    });

    if (!response.ok || !data || data.success !== true) {
      const msg =
        (data && (data.error || data.message)) ||
        "Something went wrong while creating the comment";
      showToast(msg, "error");
      return;
    }

    showToast(data.message || "Comment created", "ok");

    const commentBtn =
      postElement?.querySelector(
        `.comment-btn[data-comment-target="${postPk}"]`
      ) ||
      document.querySelector(`.comment-btn[data-comment-target="${postPk}"]`);
    const userPk = commentBtn ? commentBtn.getAttribute("data-user-pk") : null;

    await loadComments(postPk, userPk, postElement);
    form.reset();
  } catch (err) {
    console.error("Error creating comment:", err);
    showToast("Something went wrong while creating the comment", "error");
  }
});
