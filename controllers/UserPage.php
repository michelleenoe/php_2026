<?php
require_once __DIR__ . '/../x.php';
_ensureLogin('/');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/PostService.php';
require_once __DIR__ . '/../classes/FollowService.php';
require_once __DIR__ . '/../classes/UserService.php';

$currentUser = _currentUser();
if (!$currentUser) {
    header('Location: /');
    exit();
}

$userPk = $_GET['user_pk'] ?? null;
if ($userPk == $currentUser["user_pk"]) {
    header("Location: /profile");
    exit();
}

$userService = new UserService();
$profileUser = $userPk ? $userService->getActiveUserByPk($userPk) : null;

if (!$profileUser) {
    _toastError('User not found');
    header("location: /home");
    exit();
}

$postService   = new PostService();
$requestedPostPk = $_GET['post_pk'] ?? null;
if ($requestedPostPk) {
    $requested = $postService->getPostById($requestedPostPk, $currentUser["user_pk"]);
    if (!$requested) {
        _toastError('This post was deleted');
    }
}

$currentUserPk = $currentUser["user_pk"];
$filter = $_GET['filter'] ?? 'all';

$followService = new FollowService();

$isFollowing = ($currentUserPk !== $userPk) ? $followService->isFollowing($currentUserPk, $userPk) : false;

$posts = $postService->getPostsForUserWithReposts($userPk, $currentUserPk);
if ($filter === 'likes') {
    $filteredPosts = $postService->getPostsLikedByUser($userPk, $currentUserPk);
} else {
    $filteredPosts = array_values(array_filter($posts, function ($p) use ($filter) {
        if ($filter === 'posts') return empty($p['reposted_by']);
        if ($filter === 'reposts') return !empty($p['reposted_by']);
        return true;
    }));
}

$followers = $followService->getFollowersPaged($userPk, 0, 3);
$followers = array_values(array_filter($followers, fn($u) => $u['user_pk'] !== $currentUserPk));
$followersCount = $followService->countFollowers($userPk);

$usersToFollow = [];
if ($currentUserPk) {
    $usersToFollow = $followService->getSuggestions($currentUserPk, 3, 0);
    $usersToFollow = array_values(array_filter($usersToFollow, fn($u) => $u['user_pk'] !== $userPk));
}
