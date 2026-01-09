<?php require __DIR__ . '/../controllers/NotificationsPage.php'; ?>
<?php
$title = 'Notifications';
$currentPage = 'notifications';
require __DIR__ . '/../components/_header.php';
?>

<main>
    <div class="notifications-header notifications-header--padded">
        <h2 class="notifications-title">Notifications</h2>
        <?php if (array_filter($notifications, fn($n) => $n['is_read'] == 0)): ?>
  <button id="markAllBtn" class="mark-all-btn">Mark all as read</button>
<?php endif; ?>

    </div>
    <?php if (empty($notifications)): ?>
    <p class="notifications-empty">No notifications.</p>
    <?php else: ?>
    <div class="notifications-list">
        <?php foreach ($notifications as $n): ?>
        <?php
                  $unread = ($n['is_read'] == 0);
                  $hasPost = !empty($n['notification_post_fk']);
                  $targetUrl = $hasPost
                    ? "/user?user_pk=" . urlencode($n['actor_pk']) . "&post_pk=" . urlencode($n['notification_post_fk']) . "#post-" . htmlspecialchars($n['notification_post_fk'] ?? '')
                    : "/user?user_pk=" . urlencode($n['actor_pk']);
                ?>
        <div class="post <?php if($unread) echo 'notification--unread'; ?> notification-row"
            data-notif-pk="<?php echo htmlspecialchars($n['notification_pk'] ?? ''); ?>">
            <a href="<?php echo $targetUrl; ?>" class="notif-link notif-link--row">
                <img src="/public/img/avatar.jpg" data-avatar="<?php echo htmlspecialchars($n['user_avatar'] ?? ''); ?>"
                    class="profile-avatar" alt="Profile" />
                <div class="noti-content">
                    <div class="noti-header">
                        <div class="noti-name">
                            <span class="name"><?php echo htmlspecialchars($n['user_full_name']); ?></span>
                            <span class="handle"><?php echo htmlspecialchars("@" . $n['user_username']); ?></span>
                        </div>
                        <span class="time"><?php echo timeAgo($n['created_at']); ?></span>
                    </div>
                    <div class="text">
                        <?php echo htmlspecialchars($n['notification_message']); ?>
                    </div>
                </div>
            </a>
            <div class="notif-actions">
                <?php if ($unread): ?>
                <button class="notif-mark-btn" data-notif-pk="<?php echo $n['notification_pk']; ?>">
                    Mark
                </button>
                <?php endif; ?>

                <button class="notif-delete-btn" data-notif-pk="<?php echo $n['notification_pk']; ?>">Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../components/_aside.php'; ?>


<?php require __DIR__ . '/../components/_footer.php'; ?>