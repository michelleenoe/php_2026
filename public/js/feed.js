import { flipBtn } from "./flip-btn.js";
import { setupRepostButtons } from "./repost.js";

function bindComments(root) {
  if (window.bindCommentButtons) window.bindCommentButtons(root);
}

function hydrate(root) {
  if (window.hydrateAvatars) window.hydrateAvatars(root);
  if (window.mix_convert) {
    try {
      window.mix_convert();
    } catch (err) {
      console.warn("mix_convert failed", err);
    }
  }
}

export function setupFeedInfiniteScroll() {
  const feed = document.getElementById("homeFeed");
  if (!feed) return;

  const limit = parseInt(feed.dataset.feedLimit || 25, 10);
  let offset = parseInt(feed.dataset.feedOffset || feed.children.length, 10);
  let hasMore = feed.dataset.feedHasMore === "1";
  let loading = false;

  const sentinel = document.createElement("div");
  sentinel.style.height = "1px";
  feed.appendChild(sentinel);

  const loader = document.createElement("div");
  loader.className = "feed-loader";
  loader.innerHTML = '<div class="page-loader-spinner"></div>';
  loader.style.display = "none";
  feed.appendChild(loader);

  const observer = new IntersectionObserver(onIntersect, { rootMargin: "200px" });
  observer.observe(sentinel);

  function onIntersect(entries) {
    if (entries[0].isIntersecting) loadMore();
  }

  function loadMore() {
    if (loading || !hasMore) return;
    loading = true;
    loader.style.display = "flex";

    fetch(`/api-get-feed?offset=${offset}&limit=${limit}`, {
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then((res) => res.text().then((txt) => ({ ok: res.ok, status: res.status, text: txt })))
      .then(({ ok, status, text }) => {
        if (!ok) throw new Error("Request failed: " + status);
        let data;
        try {
          data = JSON.parse(text);
        } catch (err) {
          throw new Error("Invalid JSON: " + text.slice(0, 60));
        }

        if (data && data.html) {
          sentinel.insertAdjacentHTML("beforebegin", data.html);
          setupRepostButtons();
          flipBtn(feed);
          bindComments(feed);
          hydrate(feed);
          if (typeof window.hydratePostImages === "function") {
            window.hydratePostImages(feed);
          }
        }

        offset = data.nextOffset || offset + (data.count || 0);
        hasMore = data.hasMore ? true : false;

        if (!hasMore) observer.unobserve(sentinel);
      })
      .catch((err) => {
        console.error("Could not load more posts:", err);
        if (typeof showToast === "function") {
          showToast("Kunne ikke hente flere opslag lige nu.");
        }
      })
      .finally(() => {
        loading = false;
        loader.style.display = hasMore ? "flex" : "none";
      });
  }
}
