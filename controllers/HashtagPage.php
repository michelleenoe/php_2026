<?php
require_once __DIR__ . '/../x.php';
_ensureLogin('/');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/PostService.php';

$currentUser = _currentUser();
if (!$currentUser) {
    header('Location: /');
    exit();
}

$tagFromRoute = isset($tag) ? $tag : null;
$tag = $tagFromRoute ?? ($_GET['tag'] ?? null);
if (!$tag) {
    $posts = [];
    $hashtag = '';
    return;
}

$postService = new PostService();
$posts = $postService->getPostsByHashtag($tag, $_SESSION["user"]["user_pk"] ?? null);
$hashtag = "#" . htmlspecialchars($tag);

$homeSearchValue = $hashtag;
