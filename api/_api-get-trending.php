<?php
session_start();
require_once __DIR__ . '/../x.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';
require_once __DIR__ . '/../classes/TrendingService.php';

$db = _db();

class TrendingApi extends BaseApiController {}
$api = new TrendingApi();
$isAuthed = isset($_SESSION['user']);

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 2;
$offset = max(0, $offset);
$limit  = max(1, min(10, $limit));

try {
    if (!$isAuthed) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode([]);
        exit;
    }
    $trendingService = new TrendingService();
    $trending = $trendingService->getTrending($limit, $offset);
    $out = [];
    foreach ($trending as $item) {
        $out[] = [
            "topic"      => $item['tag'],
            "post_count" => $item['count']
        ];
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out);
} catch (Exception $e) {
    $api->serverError(['success' => false, 'message' => $e->getMessage()]);
}
