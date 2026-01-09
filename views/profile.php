<?php require __DIR__ . '/../controllers/ProfilePage.php'; ?>
<?php
$title = 'Profile: ' . ($currentUser['user_full_name'] ?? '');
$currentPage = 'profile';
require __DIR__ . '/../components/_header.php';
?>

<main>

    <div class="profile-header">
<?php
    $coverSrc = $currentUser['user_cover'] ?? '';
    if (!$coverSrc) {
        $pk = $currentUser['user_pk'] ?? '';
        $coverSrc = $pk ? "https://picsum.photos/seed/{$pk}/800/300" : '/public/img/cover_placeholder.png';
    }
?>

        <div class="profile-cover-container">
            <img
                src="<?php echo htmlspecialchars($coverSrc); ?>"
                alt="Cover"
                class="profile-cover"
                id="coverPreview">
            <form action="/api/api-upload-image.php"
                method="POST"
                enctype="multipart/form-data"
                class="cover-upload-form">
                <input type="hidden" name="type" value="cover">

                <label class="cover-upload-btn">
                    <i class="fa-solid fa-camera"></i>
                    <input type="file" name="file" accept="image/*" hidden>
                </label>

                <button type="submit" class="cover-save-btn">
                    Save
                </button>
            </form>
            <div class="profile-cover-filter"></div>
        </div>
        <div class="profile-page-info">
            <div class="profile-left-section">
                <form action="/api/api-upload-image.php"
                    method="POST"
                    enctype="multipart/form-data"
                    class="avatar-upload-form">
                    <input type="hidden" name="type" value="avatar">

                    <div class="avatar-wrapper">
                        <img
                            src="<?php echo htmlspecialchars($currentUser['user_avatar'] ?: '/public/img/avatar.jpg'); ?>"
                            data-avatar="<?php echo htmlspecialchars($currentUser['user_avatar'] ?? ''); ?>"
                            alt="Profile"
                            class="profile-avatar" />

                        <label class="avatar-edit-btn">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file" name="file" accept="image/*" hidden>
                        </label>
                    </div>
                </form>
            </div>
            <div class="profile-right-section">
                <div class="profile-details">
                    <h1><?php echo htmlspecialchars($currentUser["user_full_name"]); ?></h1>
                    <p>@<?php echo htmlspecialchars($currentUser["user_username"]); ?></p>
                    <p class="profile-email"><?php echo htmlspecialchars($currentUser["user_email"] ?? ''); ?></p>
                </div>
                <!-- <div class="profile-stats-inline">
                    <span><strong><?php echo count($posts); ?></strong> Posts</span>
                    <span><strong><?php echo count($followers); ?></strong> Followers</span>
                    <span><strong><?php echo $followingCount; ?></strong> Following</span>
                </div> -->
            </div>
        </div>

        <div class="profile-tabs">
            <div class="profile-tab" data-tab="posts">
                <span>Posts</span>
                <span class="tab-count">(<?php echo count($posts); ?>)</span>
            </div>
            <div class="profile-tab" data-tab="followers">
                <span>Followers</span>
                <span class="tab-count">(<?php echo count($followers); ?>)</span>
            </div>
            <div class="profile-tab" data-tab="following">
                <span>Following</span>
                <span class="tab-count">(<?php echo $followingCount; ?>)</span>
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
                <li><a class="<?php echo $filter === 'all' ? 'active' : ''; ?>" href="/profile">All</a></li>
                <li><a class="<?php echo $filter === 'posts' ? 'active' : ''; ?>" href="/profile?filter=posts">Posts</a></li>
                <li><a class="<?php echo $filter === 'reposts' ? 'active' : ''; ?>" href="/profile?filter=reposts">Reposts</a></li>
                <li><a class="<?php echo $filter === 'likes' ? 'active' : ''; ?>" href="/profile?filter=likes">Liked posts</a></li>
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
    <script src="/public/js/profile.js"></script>
    <script src="/public/js/auto-reload.js"></script>

    <script>
        setupAutoReload({
            selectors: ['.follow-btn', '.unfollow-btn'],
            delay: 200
        });
    </script>
</main>

<?php
$followLimit = $followLimit ?? 3;
require __DIR__ . '/../components/_aside.php'; ?>

<?php require __DIR__ . '/../components/_footer.php'; ?>
