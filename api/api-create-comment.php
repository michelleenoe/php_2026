<?php
session_start();
require_once __DIR__ . '/../x.php';
require_once __DIR__ . '/../classes/CommentService.php';

_ensureLogin('/');
$db = _db();
try {
    $commentTargetPk = _validatePk('post_pk');
    $commentMessage = _validateComment();

    $commentService = new CommentService();
    
    // Determine if the target is a post or repost
    $db = _db();
    $checkRepost = $db->prepare("SELECT repost_pk FROM reposts WHERE repost_pk = :pk LIMIT 1");
    $checkRepost->bindValue(':pk', $commentTargetPk);
    $checkRepost->execute();
    
    $targetType = 'post';
    if ($checkRepost->fetchColumn()) {
        $targetType = 'repost';
    } else {
        // Double-check that it's a valid post
        $checkPost = $db->prepare("SELECT post_pk FROM posts WHERE post_pk = :pk AND deleted_at IS NULL LIMIT 1");
        $checkPost->bindValue(':pk', $commentTargetPk);
        $checkPost->execute();
        if (!$checkPost->fetchColumn()) {
            throw new Exception("Post not found", 404);
        }
    }

    $result = $commentService->addCommentToTarget(
        $_SESSION["user"]["user_pk"],
        $commentTargetPk,
        $commentMessage,
        $targetType
    );

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => $result
    ]);
} catch (Exception $e) {
    error_log("[api-create-comment] " . $e->getMessage());
    try { _toastError($e->getMessage()); } catch (Exception $_) {}

    http_response_code($e->getCode() >= 400 ? $e->getCode() : 400);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'message' => $e->getMessage()
    ]);
}
