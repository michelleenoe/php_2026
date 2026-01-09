<?php
session_start();
require_once __DIR__ . '/../x.php';
require_once __DIR__ . '/../classes/FollowService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

$db = _db();

class FollowersApi extends BaseApiController {}
$api = new FollowersApi();

$userPk = isset($_GET['user_pk']) ? trim((string)$_GET['user_pk']) : '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit  = isset($_GET['limit'])  ? (int)$_GET['limit']  : 10;


$offset = max(0, $offset);
$limit = max(1, min(100, $limit));

if ($userPk === '') {
    http_response_code(400);
    echo json_encode([]);
    exit();
}

$followService = new FollowService();
$followers = $followService->getFollowersPaged($userPk, $offset, $limit);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($followers);
