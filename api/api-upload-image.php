<?php
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/../controllers/BaseApiController.php';
require_once __DIR__ . '/../classes/MediaService.php';
require_once __DIR__ . '/../x.php';
$db = _db();

class UploadImageApi extends BaseApiController {}
$api = new UploadImageApi();
$api->ensureLogin('/profile');

$currentUser = $api->currentUser() ?? $_SESSION["user"];
$userPk = $currentUser["user_pk"] ?? null;
$type = $_POST['type'] ?? null;

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
    header("Location: /profile");
    exit();
}

try {
    $mediaService = new MediaService();
    $publicPath = $mediaService->handleProfileImage($_FILES['file'] ?? null, $type);

    $field = $type === 'avatar' ? 'user_avatar' : 'user_cover';

    $q = "UPDATE users SET {$field} = :img WHERE user_pk = :pk LIMIT 1";
    $stmt = $_db->prepare($q);
    $stmt->bindValue(':img', $publicPath);
    $stmt->bindValue(':pk', $userPk);
    $stmt->execute();

    $_SESSION["user"][$field] = $publicPath;

    _toastRedirect("Image updated", "ok", "/profile");
    header("Location: /profile");
    exit();
} catch (Exception $e) {
    _toastError($e->getMessage());
    header("Location: /profile");
    exit();
}
