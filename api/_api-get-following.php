<?php
session_start();
require_once __DIR__ . '/../x.php';
require_once __DIR__ . '/../classes/FollowService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

$db = _db();

class FollowingApi extends BaseApiController {}
$api = new FollowingApi();
$currentUser = $_SESSION['user'] ?? null;
if (!$currentUser) {
    http_response_code(401);
    echo json_encode([]);
    exit();
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit  = isset($_GET['limit'])  ? (int)$_GET['limit']  : 10;

$offset = max(0, $offset);
$limit = max(1, min(100, $limit));

$followService = new FollowService();
$rows = $followService->getFollowingPaged($currentUser['user_pk'], $offset, $limit);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows);
