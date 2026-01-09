<?php
$postPk         = htmlspecialchars($post["post_pk"]);
$commentTarget  = htmlspecialchars($post["comment_target_pk"] ?? $post["post_pk"]);
?>
<article class="post" id="post-<?php echo $commentTarget; ?>" data-post-pk="<?php echo $postPk; ?>" data-comment-target="<?php echo $commentTarget; ?>">

    <div class="post-content">
        <?php if (!empty($post["reposted_by"])): ?>
            <div class="repost-banner">
                <i class="fa-solid fa-retweet"></i>
                <span class="repost-content">
                    <a class="repost-actor" href="/user?user_pk=<?php echo htmlspecialchars($post["reposted_by"]); ?>">
                        <?php
                        $actorName = htmlspecialchars($post["reposted_by_username"] ?? 'Someone');
                        echo "@{$actorName}";
                        ?>
                    </a>
                    <span class="repost-tag">reposted</span>
                </span>
            </div>
        <?php endif; ?>


        <div class="post-header">
            <div class="avatar-auth">
                <a href="/user?user_pk=<?php echo htmlspecialchars($post["post_user_fk"]); ?>">
                    <img
                        src="/public/img/avatar.jpg"
                        data-avatar="<?php echo htmlspecialchars($post['user_avatar'] ?? ''); ?>"
                        class="avatar"
                        alt="Profile">
                </a>

                <div class="post-author">
                    <a class="post-author-link" href="/user?user_pk=<?php echo htmlspecialchars($post["post_user_fk"]); ?>">
                        <span class="name"><?php echo htmlspecialchars($post["user_full_name"]); ?></span>
                        <span class="handle">@<?php echo htmlspecialchars($post["user_username"] ?? ''); ?></span>
                    </a>
                </div>
            </div>
            <div class="post-time">
                <?php
                $createdAtRaw = $post["display_created_at"] ?? $post["created_at"] ?? date('c');
                $createdAt = new DateTime($createdAtRaw);
                $now = new DateTime();
                $interval = $now->diff($createdAt);

                if ($interval->days > 0) {
                    echo $createdAt->format('j M');
                } else if ($interval->h > 0) {
                    echo $interval->h . " hours ago";
                } else if ($interval->i > 0) {
                    echo $interval->i . " minutes ago";
                } else {
                    echo "Just now";
                }

                if (!empty($post["updated_at"])) {
                    echo " · Edited";
                }
                ?>
            </div>
        </div>


        <p class="text">
            <?php
            $message = htmlspecialchars($post["post_message"]);
            $message = preg_replace(
                '/#(\w+)/',
                '<a class="hashtag-link" href="/hashtag/$1">#$1</a>',
                $message
            );
            echo $message;
            ?>
        </p>

        <?php if (!empty($post["post_image_path"])): ?>
            <a href="/user?user_pk=<?php echo htmlspecialchars($post["post_user_fk"]); ?>">
                <img
                    src="/public/img/cover_placeholder.png"
                    data-post-src="<?php echo htmlspecialchars($post["post_image_path"]); ?>"
                    alt="Post image"
                    class="post-image">
            </a>
        <?php endif; ?>



        <div class="post-actions">
            <span class="action comment-btn" data-post-pk="<?php echo $commentTarget; ?>" data-comment-target="<?php echo $commentTarget; ?>" data-user-pk="<?php echo $_SESSION["user"]["user_pk"]; ?>">
                <i id="comment_<?php echo $commentTarget; ?>" class="fa-regular fa-comment"></i>
                <span class="comment-count"><?php echo $post['comment_count'] ?? 0; ?></span>
            </span>
            <span class="action repost-btn <?php echo !empty($post['is_reposted_by_user']) ? 'active' : ''; ?>" data-post-pk="<?php echo htmlspecialchars($post["post_pk"]); ?>">
                <i id="retweet_<?php echo htmlspecialchars($post["post_pk"]); ?>" class="fa-solid fa-retweet"></i>
                <span class="repost-count"><?php echo $post['repost_count'] ?? 0; ?></span>
            </span>
            <span class="action flip-btn" data-post-pk="<?php echo htmlspecialchars($post["post_pk"]); ?>" data-comment-target="<?php echo $commentTarget; ?>">
                <i id="like_<?php echo htmlspecialchars($post["post_pk"]); ?>" class="<?php echo $post['is_liked_by_user'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                <span class="like-count"><?php echo $post['like_count'] ?? 0; ?></span>
            </span>
            <?php if ($post["post_user_fk"] === $_SESSION["user"]["user_pk"]): ?>
                <span class="action">
                    <i class="fa-solid fa-ellipsis update-post-btn" data-post-pk="<?php echo htmlspecialchars($post["post_pk"]); ?>" data-open="updatePostDialog"></i>
                </span>
            <?php endif; ?>
        </div>




        <div class="comment-dialog" id="commentDialog_<?php echo $commentTarget; ?>" style="display: none;">
            <div class="comments-container" id="commentsContainer_<?php echo $commentTarget; ?>" data-comment-target="<?php echo $commentTarget; ?>"></div>
            <form class="comment-form" data-post-pk="<?php echo $commentTarget; ?>">
                <textarea name="comment_message" class="comment_message" placeholder="Write a comment..." rows="1"></textarea>
                <button class="comment-form_btn" type="submit">Comment</button>
            </form>
        </div>
    </div>
</article>

<template id="commentTemplate">
    <div class="comment" data-comment-pk="">
        <span class="edited"></span>
        <div class="comment-header">
            <div class="avatar-auth-comt">
                <a class="comment-author-link" href="#">
                    <img
                        src="/public/img/avatar.jpg"
                        class="comment-avatar"
                        data-avatar=""
                        alt="Profile">
                </a>


                <div class="comment-author">
                    <a class="comment-author-link" href="#">
                        <span class="name"></span>
                        <span class="handle"></span>
                    </a>
                </div>
                <span class="time"></span>

            </div>

            <div class="comment-opt">
                <div class="comment-actions">
                    <button class="edit-comment-btn">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="delete-comment-btn">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>

        <p class="comment-text"></p>

        <form class="edit-comment-form" style="display:none;">
            <textarea class="edit-comment-textarea" name="comment_message"></textarea>
            <div class="comment-form-btns">
                <button type="submit" class="save-comment-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>

                <button type="button" class="cancel-edit-btn">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </form>
    </div>
</template>

<template id="deleteConfirmTemplate">
    <div class="delete-confirm-comment delete-confirm--visible">
        <span class="delete-confirm__message"></span>
        <div class="delete-confirm__buttons">
            <button class="delete-confirm__btn delete-confirm__btn--secondary">Cancel</button>
            <button class="delete-confirm__btn delete-confirm__btn--danger">Yes</button>
        </div>
    </div>
</template>