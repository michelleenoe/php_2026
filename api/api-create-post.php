<?php 
ini_set('display_errors', '0');

session_start();
require_once __DIR__."/../x.php";
require_once __DIR__ . '/../controllers/BaseApiController.php';
require_once __DIR__ . '/../classes/MediaService.php';
require_once __DIR__ . '/../classes/PostService.php';

$db = _db();
class CreatePostApi extends BaseApiController {}
$api = new CreatePostApi();
$api->ensureLogin('/');

$user = $api->currentUser() ?? $_SESSION["user"] ?? null;

function _toBytes(string $val): int {
    $val = trim($val);
    $last = strtolower(substr($val, -1));
    $num = (int)$val;
    switch ($last) {
        case 'g': $num *= 1024;
        case 'm': $num *= 1024;
        case 'k': $num *= 1024;
    }
    return $num;
}
$maxPost = _toBytes(ini_get('post_max_size') ?: '8M');
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > $maxPost) {
    _toastError('File too large (max 3 MB)');
    $_SESSION['open_dialog'] = 'post';
    $redirect = _redirectPath('/home');
    header('Location: ' . $redirect);
    exit();
}

try {
    $postMessage = _validatePost();
    $mediaService = new MediaService();
    $postImage = $mediaService->handlePostImageUpload($_FILES["post_image"] ?? []);

    $postPk = bin2hex(random_bytes(25));

    $postService = new PostService();
    $postService->createPost($postPk, $postMessage, $postImage, $user["user_pk"]);

    try {
        require_once __DIR__ . '/../models/NotificationModel.php';
        $nm = new NotificationModel();
        $notifMessage = mb_strimwidth(strip_tags($postMessage), 0, 200, '...');
        $nm->createForFollowers($user['user_pk'], $postPk, $notifMessage);
    } catch (Exception $e) {
        error_log('[api-create-post] Notification create failed: ' . $e->getMessage());
    }

    unset($_SESSION['old_post_message']);
    $redirect = _redirectPath('/home');
    _toastRedirect('Post created!', 'ok', $redirect);
}
catch(Exception $e){
    _toastError($e->getMessage());
    $_SESSION['open_dialog'] = 'post';
    if (!empty($_POST['post_message'])) {
        $_SESSION['old_post_message'] = $_POST['post_message'];
    }
    $redirect = _redirectPath('/home');
    header('Location: ' . $redirect);
    exit();
}
