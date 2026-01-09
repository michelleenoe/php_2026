<?php
require_once __DIR__ . '/../x.php';
_ensureLogin('/');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/TrendingModel.php';
require_once __DIR__ . '/../models/FollowModel.php';

$postModel     = new PostModel();
$trendingModel = new TrendingModel();
$followModel   = new FollowModel();

$currentUserPk = $_SESSION["user"]["user_pk"];

$feedLimit = 25;
$posts = $postModel->getPostsForFeed($feedLimit + 1, $currentUserPk, 0);
$feedHasMore = count($posts) > $feedLimit;
if ($feedHasMore) {
    array_pop($posts);
}

$limit    = 4;
$offset   = 0;
$maxItems = 10;

$trending = $trendingModel->getTrending($limit, $offset);

$followLimit = 3;
$usersToFollow = $followModel->getSuggestions($currentUserPk, $followLimit, 0);
$initialFollowCount = count($usersToFollow);
