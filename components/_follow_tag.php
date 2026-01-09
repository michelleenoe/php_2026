<a href="/user?user_pk=<?php echo $user['user_pk']; ?>" class="profile-info" id="<?php echo $user["user_pk"]; ?>">
<img
    src="/public/img/avatar.jpg"
    data-avatar="<?php echo htmlspecialchars($user['user_avatar'] ?? ''); ?>"
    class="profile-avatar"
    alt="Profile"
/>
    <div class="info-copy">
        <p class="name"><?php echo htmlspecialchars($user["user_full_name"]); ?></p>
        <p class="handle"><?php echo htmlspecialchars("@" . $user["user_username"]); ?></p>
    </div>
    <?php
    $user_pk = $user["user_pk"];
    $isFollowing = false;
    if (isset($_SESSION["user"])) {
        require_once __DIR__ . '/../classes/FollowService.php';
        $followService = $followService ?? new FollowService();
        $isFollowing = $followService->isFollowing($_SESSION["user"]["user_pk"], $user_pk);
    }

    if ($isFollowing) {
        require __DIR__ . '/___button_unfollow.php';
    } else {
        require __DIR__ . '/___button_follow.php';
    }
    ?>
</a>
