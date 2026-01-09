<?php

require_once __DIR__ . '/../x.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


if (!empty($requireLogin) && function_exists('_ensureLogin')) {
    _ensureLogin('/');
}


$notifCount = 0;
$user = _currentUser();
if ($user) {
    try {
        require_once __DIR__ . '/../models/NotificationModel.php';
        $nm = new NotificationModel();
        if (method_exists($nm, 'countUnreadForUser')) {
            $notifCount = $nm->countUnreadForUser($user['user_pk']);
        }
    } catch (Throwable $_) {
        $notifCount = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/public/favicon/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

 
    <link rel="stylesheet" href="/public/css/app.css">
    <link rel="stylesheet" href="/public/css/search.css">
    <link rel="stylesheet" href="/public/css/burger-overlay.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <script type="module" src="/public/js/app.js"></script>
    <script defer src="/public/js/dialog.js"></script>
    <script defer src="/public/js/handle-dialogs.js"></script>
    <script src="/public/js/toast.js"></script>
    <script defer src="/public/js/comment.js"></script>
    <script defer src="/public/js/load-more-btn.js"></script>
    <script defer src="/public/js/avatar-loader.js"></script>

    <title><?= htmlspecialchars($title ?? '') ?></title>
</head>
<body>
<div class="page-loader" id="pageLoader">
    <div class="page-loader-spinner"></div>
</div>
<script>

window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(function() {
            loader.classList.add('page-loader--hidden');
            setTimeout(function() { loader.remove(); }, 300);
        }, 100);
    }
});
</script>

<?php require_once __DIR__ . '/___toast.php'; ?>


<div id="container">
    <button class="burger" aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
        <i class="fa-solid fa-xmark"></i>
    </button>

    <?php
    require_once __DIR__ . '/_nav.php'; ?>
      <?php
    if (file_exists(__DIR__ . '/_post-dialog.php')) require_once __DIR__ . '/_post-dialog.php';
    if (file_exists(__DIR__ . '/_update-profile-dialog.php')) require_once __DIR__ . '/_update-profile-dialog.php';
    if (file_exists(__DIR__ . '/_update-post-dialog.php')) require_once __DIR__ . '/_update-post-dialog.php';

  ?>