<?php require __DIR__ . '/../controllers/UserPage.php'; ?>
<?php
$title = $profileUser["user_full_name"] . " (@" . $profileUser["user_username"] . ")";
$currentPage = 'user';
require __DIR__ . '/../components/_header.php';
?>

<main>
    <div class="profile-header">
        <div class="profile-cover-container">
            <?php
                $coverSrc = $profileUser['user_cover'] ?? '';
                if (!$coverSrc) {
                    $pk = $profileUser['user_pk'] ?? '';
                    $coverSrc = $pk ? "https://picsum.photos/seed/{$pk}/800/300" : '/public/img/cover_placeholder.png';
                }
            ?>
            <img src="<?php echo htmlspecialchars($coverSrc); ?>"
                alt="Cover" class="profile-cover">
            <div class="profile-cover-filter"></div>
        </div>
        <div class="profile-page-info">
            <img
                src="<?php echo htmlspecialchars($profileUser['user_avatar'] ?? '/public/img/avatar.jpg'); ?>"
                data-avatar="<?php echo htmlspecialchars($profileUser['user_avatar'] ?? ''); ?>"
                alt="Profile"
                class="profile-avatar profile-left-section" />
            <div class="user-info">
                <div>
                    <h1><?php echo htmlspecialchars($profileUser["user_full_name"]); ?></h1>
                    <p>@<?php echo htmlspecialchars($profileUser["user_username"]); ?></p>
                    <div class="profile-stats">
                        <span><strong><?php echo count($posts); ?></strong> Posts</span>
                        <span><strong><?php echo isset($followersCount) ? $followersCount : count($followers); ?></strong> Followers</span>
                    </div>
                </div>
                <div>

                    <?php if ($userPk !== $currentUserPk): ?>
                        <div class="follow-button-container">
                            <?php if ($isFollowing): ?>
                                <button class="unfollow-btn button-<?php echo $userPk; ?>"
                                    mix-get="api-unfollow?user-pk=<?php echo $userPk; ?>">
                                    Unfollow
                                </button>
                            <?php else: ?>
                                <button class="follow-btn button-<?php echo $userPk; ?>"
                                    mix-get="api-follow?user-pk=<?php echo $userPk; ?>">
                                    Follow
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-feed-filter">
        <div class="profile-filter">
            <button class="filter-toggle" aria-haspopup="listbox" aria-expanded="false">
                <span class="filter-label">
                    <?php
                    if ($filter === 'posts') echo 'Posts';
                    else if ($filter === 'reposts') echo 'Reposts';
                    else if ($filter === 'likes') echo 'Liked posts';
                    else echo 'All';
                    ?>
                </span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <ul class="filter-menu" role="listbox" aria-label="Filter posts">
                <li><a class="<?php echo $filter === 'all' ? 'active' : ''; ?>" href="/user?user_pk=<?php echo urlencode($userPk); ?>">All</a></li>
                <li><a class="<?php echo $filter === 'posts' ? 'active' : ''; ?>" href="/user?user_pk=<?php echo urlencode($userPk); ?>&filter=posts">Posts</a></li>
                <li><a class="<?php echo $filter === 'reposts' ? 'active' : ''; ?>" href="/user?user_pk=<?php echo urlencode($userPk); ?>&filter=reposts">Reposts</a></li>
                <li><a class="<?php echo $filter === 'likes' ? 'active' : ''; ?>" href="/user?user_pk=<?php echo urlencode($userPk); ?>&filter=likes">Liked posts</a></li>
            </ul>
        </div>
    </div>

    <?php if (empty($filteredPosts)): ?>
        <p class="no-posts">No posts yet.</p>
    <?php else: ?>
        <?php foreach ($filteredPosts as $post): ?>
            <?php require __DIR__ . "/../components/_post.php"; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php
$followLimit = $followLimit ?? 3;
require __DIR__ . '/../components/_aside.php'; ?>

<?php require __DIR__ . '/../components/_footer.php'; ?>

<script src="/public/js/profile.js"></script>

<?php
