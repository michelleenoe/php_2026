<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
require_once __DIR__ . '/../classes/PostService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

class UpdatePostApi extends BaseApiController {}
$api = new UpdatePostApi();
_ensureLogin('/');

try {
    $postPk      = $_POST['post_pk'] ?? null;
    $postMessage = _validatePost(); 

    if (!$postPk) {
        throw new Exception("Post ID is required", 400);
    }

    $redirect = _redirectPath('/home');

    $postService = new PostService();
    $postService->updatePost($postPk, $postMessage, $_SESSION['user']['user_pk']);

    _toastOk('Post updated!');
    header("Location: " . $redirect);
    exit();

} catch (Exception $e) {
    _toastError($e->getMessage());
    $_SESSION['open_dialog']             = 'update';
    $_SESSION['old_update_post_pk']      = $_POST['post_pk'] ?? null;
    $_SESSION['old_update_post_message'] = $_POST['post_message'] ?? '';
    $redirect = $redirect ?? _redirectPath('/home');
    header("Location: " . $redirect);
    exit();
}
