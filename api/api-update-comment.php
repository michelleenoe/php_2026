<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
require_once __DIR__ . '/../classes/CommentService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

class UpdateCommentApi extends BaseApiController {}
$api = new UpdateCommentApi();
_ensureLogin('/');

try {
    $commentPk      = _validatePk('comment_pk');
    $commentMessage = _validateComment();

    $cs = new CommentService();
    $cs->updateComment($commentPk, $commentMessage, $_SESSION["user"]["user_pk"]);

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
