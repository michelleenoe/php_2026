function setupLoadMore({ buttonId, listId, url, defaultLimit = 10, handleNonOk, renderItem }) {
  const btn = document.getElementById(buttonId);
  const list = document.getElementById(listId);
  if (!btn || !list) return;

  const initialCount = Number(btn.dataset.initial || list.children.length || 0);
  const maxItems = Number(btn.dataset.max || Infinity);

  btn.dataset.mode = btn.dataset.mode || "more";
  btn.dataset.offset = btn.dataset.offset || initialCount;

  const userPk = btn.dataset.userPk || null;

  btn.addEventListener("click", async () => {
    const mode = btn.dataset.mode;

    if (mode === "less") {
      console.debug("[load-more] show less clicked", { buttonId, initialCount, current: list.children.length });
      while (list.children.length > initialCount) {
        list.removeChild(list.lastElementChild);
      }
      btn.dataset.mode = "more";
      btn.textContent = "Show more";
      btn.style.display = "";
      btn.dataset.offset = initialCount;
      return;
    }

    const offset = Number(btn.dataset.offset);
    const limit = Number(btn.dataset.limit || defaultLimit);

    const requestUrl = new URL(url, window.location.origin);
    requestUrl.searchParams.set("offset", offset);
    requestUrl.searchParams.set("limit", limit);
    if (userPk) requestUrl.searchParams.set("user_pk", userPk);

    try {
      const res = await fetch(requestUrl.toString());
      if (!res.ok) {
        if (handleNonOk && handleNonOk(res, btn) === false) return;
        throw new Error(`Request failed with status ${res.status}`);
      }

      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {

        const totalNow = list.children.length;
        if (totalNow > initialCount) {

          btn.dataset.mode = "less";
          btn.textContent = "Show less";
          btn.style.display = "";
        } else {

          btn.style.display = "none";
        }
        return;
      }

      data.forEach((item) => renderItem(item, list));
      if (typeof window.hydrateAvatars === "function") {
        window.hydrateAvatars(list);
      }

      if (typeof window.mix_convert === "function") {
        try {
          window.mix_convert();
        } catch (err) {
          console.warn("mix_convert failed", err);
        }
      }

      const total = list.children.length;
      btn.dataset.offset = offset + data.length;
      console.debug("[load-more] appended items", { id: buttonId, appended: data.length, total });

      if (total >= maxItems) {
 
        btn.dataset.mode = "less";
        btn.textContent = "Show less";
        btn.style.display = "";
      } else if (data.length < limit) {
      
        if (total > initialCount) {
          btn.dataset.mode = "less";
          btn.textContent = "Show less";
          btn.style.display = "";
        } else {
          btn.style.display = "none";
        }
      } else {
        btn.dataset.mode = "more";
        btn.textContent = "Show more";
        btn.style.display = "";
      }
    } catch (err) {
      console.error("Load-more error:", err);
    }
  });
}
function renderUserRow(user, list, { buttonText, buttonClass, endpoint }) {
  const a = document.createElement("a");
  a.href = `/user?user_pk=${user.user_pk}`;
  a.className = "profile-info";
  a.id = user.user_pk;

  const img = document.createElement("img");
  const avatarSrc = user.user_avatar && user.user_avatar.trim() !== "" ? user.user_avatar : "/public/img/avatar.jpg";
  img.src = avatarSrc;
  img.dataset.avatar = avatarSrc;
  img.className = "profile-avatar";

  const info = document.createElement("div");
  info.className = "info-copy";

  const nameEl = document.createElement("p");
  nameEl.className = "name";
  nameEl.textContent = user.user_full_name;

  const handleEl = document.createElement("p");
  handleEl.className = "handle";
  handleEl.textContent = `@${user.user_username}`;

  info.append(nameEl, handleEl);

  const btn = document.createElement("button");
  btn.className = `${buttonClass} button-${user.user_pk}`;
  btn.setAttribute("mix-get", `${endpoint}?user-pk=${user.user_pk}`);
  btn.textContent = buttonText;

  a.append(img, info, btn);
  list.appendChild(a);
}

setupLoadMore({
  buttonId: "trendingShowMore",
  listId: "trendingList",
  url: "/api/_api-get-trending.php",
  defaultLimit: 2,
  renderItem(item, list) {
    const raw = (item.topic || "").trim();
    const tag = raw || "#?";
    const clean = tag.startsWith("#") ? tag.slice(1) : tag;

    const div = document.createElement("div");
    div.className = "trending-item";
    div.innerHTML = `
        <div class="trending-info">
          <span class="item_title">Trending · ${item.post_count} posts</span>
          <p><a href="/hashtag/${clean}" class="hashtag-link">${tag}</a></p>
        </div>
      `;
    list.appendChild(div);
  },
});

setupLoadMore({
  buttonId: "followShowMore",
  listId: "whoToFollowList",
  url: "/api/_api-get-who-to-follow.php",
  defaultLimit: 3,
  handleNonOk(res, btn) {
    if (res.status === 401) {
      btn.style.display = "none";
      return false;
    }
    return true;
  },
  renderItem(user, list) {
    renderUserRow(user, list, {
      buttonText: "Follow",
      buttonClass: "follow-btn",
      endpoint: "api-follow",
    });
  },
});


setupLoadMore({
  buttonId: "followingShowMore",
  listId: "followingList",
  url: "/api/_api-get-following.php",
  defaultLimit: 3,
  handleNonOk(res, btn) {
    if (res.status === 401) {
      btn.style.display = "none";
      return false;
    }
    return true;
  },
  renderItem(user, list) {
    renderUserRow(user, list, {
      buttonText: "Unfollow",
      buttonClass: "unfollow-btn",
      endpoint: "api-unfollow",
    });
  },
});


setupLoadMore({
  buttonId: "followersShowMore",
  listId: "followersList",
  url: "/api/_api-get-followers.php",
  defaultLimit: 3,
  renderItem(user, list) {
    renderUserRow(user, list, {
      buttonText: "Follow",
      buttonClass: "follow-btn",
      endpoint: "api-follow",
    });
  },
});
