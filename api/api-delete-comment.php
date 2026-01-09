<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
require_once __DIR__ . '/../classes/CommentService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

class DeleteCommentApi extends BaseApiController {}
$api = new DeleteCommentApi();

if (!isset($_SESSION["user"])) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

try {
    $commentPk = $_POST['comment_pk'] ?? null;
    if (!$commentPk) {
        throw new Exception("Comment ID is required.");
    }

    $cs = new CommentService();
    $cs->deleteComment($commentPk, $_SESSION["user"]["user_pk"]);

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
