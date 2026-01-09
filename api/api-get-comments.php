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
    
    // Determine if the target is a post or repost
    $checkRepost = $db->prepare("SELECT repost_pk FROM reposts WHERE repost_pk = :pk LIMIT 1");
    $checkRepost->bindValue(':pk', $postPk);
    $checkRepost->execute();
    
    $targetType = 'post';
    if ($checkRepost->fetchColumn()) {
        $targetType = 'repost';
    } else {
        // Double-check that it's a valid post
        $checkPost = $db->prepare("SELECT post_pk FROM posts WHERE post_pk = :pk AND deleted_at IS NULL LIMIT 1");
        $checkPost->bindValue(':pk', $postPk);
        $checkPost->execute();
        if (!$checkPost->fetchColumn()) {
            throw new Exception("Post not found", 404);
        }
    }
    
    $cs = new CommentService();
    $comments = $cs->getCommentsByTarget($postPk, $targetType);
    header('Content-Type: application/json');
    echo json_encode($comments);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
