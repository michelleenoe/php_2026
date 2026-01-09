<?php
session_start();
require_once __DIR__ . '/../x.php';
_ensureLogin('/');

$db = _db();
global $_db;
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../models/PostModel.php';

    $currentUserPk = $_SESSION['user']['user_pk'] ?? null;

    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    $limit = max(1, min(50, $limit));
    $offset = max(0, $offset);

    $postModel = new PostModel();

    $posts = $postModel->getPostsForFeed($limit + 1, $currentUserPk, $offset);
    $hasMore = count($posts) > $limit;
    if ($hasMore) {
        array_pop($posts);
    }

    $html = '';
    foreach ($posts as $post) {
        ob_start();
        require __DIR__ . '/../components/_post.php';
        $html .= ob_get_clean();
    }

    echo json_encode([
        'html'       => $html,
        'count'      => count($posts),
        'hasMore'    => $hasMore,
        'nextOffset' => $offset + count($posts),
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
