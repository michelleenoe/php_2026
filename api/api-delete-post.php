<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
require_once __DIR__ . '/../classes/PostService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

class DeletePostApi extends BaseApiController {}
$api = new DeletePostApi();
_ensureLogin('/');

try {
    $postPk = $_POST['post_pk'] ?? $_GET['post_pk'] ?? null;
    $redirect = _redirectPath('/home');

    if (!$postPk) {
        throw new Exception("Post ID is required", 400);
    }

    $postService = new PostService();
    $postService->deletePost($postPk, $_SESSION["user"]["user_pk"]);

    _toastOk('Post deleted');
    header("Location: " . $redirect);
    exit;
} catch (Exception $e) {
    _toastError($e->getMessage());
    header("Location: " . ($redirect ?? '/home'));
    exit;
}
