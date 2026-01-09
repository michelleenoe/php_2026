<div class="search-results">

    <section class="search-results-section">
        <h3 class="search-results-title">Users</h3>

        <?php if (empty($users)): ?>
            <p>No users found</p>
        <?php else: ?>
            <ul class="search-results-list">
                <?php foreach ($users as $user): ?>
                    <li class="search-results-user">
                        <a href="/user?user_pk=<?php echo htmlspecialchars($user['user_pk']); ?>">
                            <div>
                                <span
                                    class="search-results-user-name"
                                    data-search-text="<?php echo htmlspecialchars($user["user_full_name"]); ?>">
                                    <?php echo htmlspecialchars($user["user_full_name"]); ?>
                                </span>

                                <span
                                    class="search-results-user-handle"
                                    data-search-text="@<?php echo htmlspecialchars($user["user_username"]); ?>">
                                    @<?php echo htmlspecialchars($user["user_username"]); ?>
                                </span>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="search-results-section">
        <h3 class="search-results-title">Posts</h3>

        <?php if (empty($posts)): ?>
            <p>No posts found</p>
        <?php else: ?>
            <div class="search-results-grid">

                <?php foreach ($posts as $post): ?>
                    <article class="search-results-post-card">
                    <a href="/user?user_pk=<?php echo htmlspecialchars($post["post_user_fk"]); ?>&post_pk=<?php echo htmlspecialchars($post["post_pk"]); ?>#post-<?php echo htmlspecialchars($post["post_pk"]); ?>">

                            <?php if (!empty($post["post_image_path"])): ?>
                                <div class="search-results-post-image-wrapper">
                                    <img
                                        src="/public/img/cover_placeholder.png"
                                        data-post-src="<?php echo htmlspecialchars($post["post_image_path"]); ?>"
                                        loading="lazy"
                                        class="search-results-post-image">
                                </div>
                            <?php endif; ?>

                            <div class="search-results-post-body">

                                <div class="search-results-post-auhtor">
                                    <span
                                        data-search-text="@<?php echo htmlspecialchars($post["user_username"]); ?>">
                                        @<?php echo htmlspecialchars($post["user_username"]); ?>
                                    </span>
                                </div>

                                <?php
                                $originalMessage = $post["post_message"];

                                $safeMessage = htmlspecialchars($originalMessage);
                                $safeMessage = preg_replace(
                                    '/#(\w+)/',
                                    '<a class="hashtag-link" href="/hashtag/$1">#$1</a>',
                                    $safeMessage
                                );
                                ?>

                                <p
                                    class="search-results-post-text"
                                    data-search-text="<?php echo htmlspecialchars($originalMessage); ?>">
                                    <?= $safeMessage ?>
                                </p>

                            </div>

                        </a>
                    </article>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>

    </section>

</div>
