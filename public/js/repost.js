export function setupRepostButtons() {
  document.querySelectorAll(".repost-btn").forEach((btn) => {
    if (btn.dataset.repostBound) return;
    btn.dataset.repostBound = "1";

    btn.addEventListener("click", async () => {
      const postPk = btn.getAttribute("data-post-pk");
      if (!postPk) return;

      const countEl = btn.querySelector(".repost-count");
      const isActive = btn.classList.contains("active");
      const currentCount = parseInt(countEl?.textContent || "0", 10);

      try {
        const res = await fetch(`/api/api-repost.php?post-pk=${postPk}`, {
          method: "GET",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        });
        if (!res.ok) throw new Error("Network error");
        
        btn.classList.toggle("active");
        if (countEl) {
          const next = isActive ? Math.max(0, currentCount - 1) : currentCount + 1;
          countEl.textContent = next;
        }
        if (typeof showToast === "function") {
          showToast(isActive ? "Repost removed" : "Post reposted");
        }
        setTimeout(() => window.location.reload(), 150);
      } catch (error) {
        console.error("Repost failed", error);
        if (typeof showToast === "function") {
          showToast("Could not toggle repost right now.");
        }
      }
    });
  });
}
