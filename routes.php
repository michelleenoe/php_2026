<?php

require_once __DIR__ . '/router.php';

// Views
get('/',               'views/index.php');
get('/home',           'views/home.php');
get('/profile',        'views/profile.php');
get('/notifications',  'views/notifications.php');
get('/user',           'views/user.php');
get('/hashtag',        'views/hashtag.php');
get('/hashtag/$tag',   'views/hashtag.php');
get('/search',         'views/search.php');
get('/signup',         'views/signup.php');
get('/test',           'views/test.php');
get('/person',         'views/person.php');
get('/proxy',          'views/proxy.php');

// Connections (form posts)
post('/bridge-login',   'connections/bridge-login.php');
post('/bridge-signup',  'connections/bridge-signup.php');
get('/bridge-logout',   'connections/bridge-logout.php');
post('/bridge-search',  'connections/bridge-search.php');

// API endpoints
get('/api-follow',                 'api/api-follow.php');
get('/api-unfollow',               'api/api-unfollow.php');
get('/api-repost',                 'api/api-repost.php');
post('/api-create-post',           'api/api-create-post.php');
post('/api-update-post',           'api/api-update-post.php');
any('/api-delete-post',            'api/api-delete-post.php');
post('/api-create-comment',        'api/api-create-comment.php');
post('/api-update-comment',        'api/api-update-comment.php');
post('/api-delete-comment',        'api/api-delete-comment.php');
get('/api-get-comments',           'api/api-get-comments.php');
get('/api-get-post',               'api/api-get-post.php');
post('/api-like-post',              'api/api-like-post.php');
post('/api-unlike-post',            'api/api-unlike-post.php');
post('/api-update-profile',        'api/api-update-profile.php');
any('/api-delete-profile',         'api/api-delete-profile.php');
get('/api-get-unread-notif-count', 'api/api-get-unread-notif-count.php');
post('/api-mark-notification-read','api/api-mark-notification-read.php');
post('/api-mark-all-notifications-read','api/api-mark-all-notifications-read.php');
post('/api-delete-notification',   'api/api-delete-notification.php');
post('/api-upload-image',          'api/api-upload-image.php');
get('/api-get-feed',               'api/api-get-feed.php');
get('/api-get-followers',          'api/_api-get-followers.php');
get('/api-get-following',          'api/_api-get-following.php');
get('/api-get-who-to-follow',      'api/_api-get-who-to-follow.php');
get('/api-get-trending',           'api/_api-get-trending.php');
get('/api-verify-email',           'api/api-verify-email.php');
get('/api-resend-verification',    'api/api-resend-verification.php');

any('/404', 'views/404.php');
