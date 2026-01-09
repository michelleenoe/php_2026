<?php
require_once __DIR__ . '/../x.php';
$user = _currentUser();

$currentPage = $currentPage ?? '';

$notifCount = $notifCount ?? 0;
if ($notifCount === 0 && $user) {
    try {
        require_once __DIR__ . '/../models/NotificationModel.php';
        $nm = new NotificationModel();
        if (method_exists($nm, 'countUnreadForUser')) {
            $notifCount = $nm->countUnreadForUser($user['user_pk']);
        } else {
            $notifCount = 0;
        }
    } catch (Throwable $e) {
        $notifCount = 0;
    }
}
?>
<nav>
    <ul class="nav-ul">
        <li>
            <a href="/home">
                <img src="/public/img/weave-logo.png" alt="Weave logo" class="nav-logo">
            </a>
        </li>
        <li><a href="/home"><span>Home</span><i class="fa-solid fa-house"></i></a></li>
        <li><a href="#" class="open-search"><span>Explore</span><i class="fa-solid fa-magnifying-glass"></i></a></li>
        <li><a href="/notifications"><span>Notifications</span><i class="fa-regular fa-bell"></i></a></li>
        <li><a href="/profile"><span>Profile</span><i class="fa-regular fa-user"></i></a></li>
        <li><a href="#" data-open="updateProfileDialog"><span>More</span><i class="fa-solid fa-ellipsis"></i></a></li>
        <li><a href="/bridge-logout"><span>Logout</span><i class="fa-solid fa-right-from-bracket"></i></a></li>
    </ul>

    <?php if ($user): ?>
        <div class="nav_btn_group">
            <button class="post-btn" data-open="postDialog">Post</button>

            <div id="profile_tag" data-open="updateProfileDialog">
                <img
                    src="/public/img/avatar.jpg"
                    data-avatar="<?php echo htmlspecialchars($user['user_avatar'] ?? ''); ?>"
                    alt="Profile">
                <div>
                <div class="name"><?= htmlspecialchars($user['user_full_name'] ?? '') ?></div>
                <div class="handle"><?= $user ? ('@' . htmlspecialchars($user['user_username'])) : '' ?></div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</nav>