export function flipBtn(root = document) {
  (root || document).querySelectorAll(".flip-btn").forEach((btn) => {
    if (btn.dataset.likeBound) return;
    btn.dataset.likeBound = "1";

    btn.addEventListener("click", function () {
      const heartIcon = this.querySelector("i");
      const postPk = this.getAttribute("data-post-pk");
      const likeCountSpan = this.querySelector(".like-count");
      if (!postPk) {
        if (typeof showToast === "function") showToast("Missing post id");
        return;
      }
      const isLiked = heartIcon.classList.contains("fa-solid");

      const url = isLiked ? `/api-unlike-post` : `/api-like-post`;
      const body = new URLSearchParams();
      body.set("post_pk", postPk);

      fetch(url, {
        method: "POST",
        body,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.json().catch(() => ({}));
        })
        .then(() => {
          if (isLiked) {
            heartIcon.classList.remove("fa-solid");
            heartIcon.classList.add("fa-regular");
            if (likeCountSpan) {
              likeCountSpan.textContent = parseInt(likeCountSpan.textContent || "0", 10) - 1;
            }
          } else {
            heartIcon.classList.remove("fa-regular");
            heartIcon.classList.add("fa-solid");
            if (likeCountSpan) {
              likeCountSpan.textContent = parseInt(likeCountSpan.textContent || "0", 10) + 1;
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          if (typeof showToast === "function") {
            showToast("Could not update like right now.");
          } else {
            alert("Something went wrong. Please try again.");
          }
          if (isLiked) {
            heartIcon.classList.remove("fa-regular");
            heartIcon.classList.add("fa-solid");
          } else {
            heartIcon.classList.remove("fa-solid");
            heartIcon.classList.add("fa-regular");
          }
        });
    });
  });
}
