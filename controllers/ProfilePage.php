<?php
require_once __DIR__ . '/../x.php';
_ensureLogin('/');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/PostService.php';
require_once __DIR__ . '/../classes/FollowService.php';

$currentUser = _currentUser();
if (!$currentUser) {
    header('Location: /');
    exit();
}

$currentUserPk = $currentUser["user_pk"];

$postService   = new PostService();
$followService = new FollowService();

$filter = $_GET['filter'] ?? 'all';
$posts          = $postService->getPostsForUserWithReposts($currentUserPk, $currentUserPk);
if ($filter === 'likes') {
    $filteredPosts = $postService->getPostsLikedByUser($currentUserPk, $currentUserPk);
} else {
    $filteredPosts  = array_values(array_filter($posts, function ($p) use ($filter) {
        if ($filter === 'posts') return empty($p['reposted_by']);
        if ($filter === 'reposts') return !empty($p['reposted_by']);
        return true;
    }));
}
$following      = $followService->getFollowing($currentUserPk, 3);
$followers      = $followService->getFollowers($currentUserPk, 3);
$followingCount = $followService->countFollowing($currentUserPk);
$followersCount = $followService->countFollowers($currentUserPk);
