(function () {
    function scrollToPost(postId) {
        if (!postId) return;


        const el = document.getElementById("post-" + postId);
        if (!el) return;


        try {
            el.scrollIntoView({ behavior: "smooth", block: "center" });
            el.classList.add("post--highlight");


            setTimeout(function () {
                el.classList.remove("post--highlight");
            }, 2500);


        } catch (e) {
            console.error("scrollToPost error:", e);
        }
    }


    document.addEventListener("DOMContentLoaded", function () {
        const params = new URLSearchParams(window.location.search);
        const postPk = params.get("post_pk");


        if (postPk) {
            scrollToPost(postPk);
            return;
        }


        if (window.location.hash && window.location.hash.indexOf("#post-") === 0) {
            const id = window.location.hash.replace("#post-", "");
            scrollToPost(id);
        }
    });
})();