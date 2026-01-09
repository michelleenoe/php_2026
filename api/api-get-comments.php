<?php
require_once __DIR__ . '/../x.php';
$db = _db();
session_start();
if (!isset($_SESSION["user"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}
try {
    require_once __DIR__ . '/../classes/CommentService.php';
    $postPk = $_GET['post_pk'] ?? null;
    if (!$postPk) {
        throw new Exception("Post ID is required.");
    }
    $cs = new CommentService();
    $comments = $cs->getCommentsWithOriginal($postPk);
    header('Content-Type: application/json');
    echo json_encode($comments);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
