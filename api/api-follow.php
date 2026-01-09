<?php

session_start();
if (!isset($_SESSION["user"])) {
    require_once __DIR__ . '/../controllers/BaseApiController.php';
    $api = new class extends BaseApiController {};
    $api->unauthorized(["error" => "Please login to follow a user"]);
}

try {
    require_once __DIR__ . '/../x.php';
    $db = _db();
    require_once __DIR__ . '/../classes/FollowService.php';
    require_once __DIR__ . '/../controllers/BaseApiController.php';
    $api = $api ?? new class extends BaseApiController {};
    $followerPk = $_SESSION["user"]["user_pk"];
    $followPk = $_GET['user-pk'];

    $followService = new FollowService();
    try {
        $followService->follow($followerPk, $followPk);
    } catch (Exception $e) {
        $code = $e->getCode() ?: 400;
        $errorCode = $code === 404 ? 'user_deleted' : 'follow_error';
        $api->json([
            'success'    => false,
            'error'      => $e->getMessage(),
            'error_code' => $errorCode,
            'user_pk'    => $followPk,
        ], $code);
    }

    $user_pk = $followPk;
    echo "<mixhtml mix-replace='.button-$user_pk'>";
    require __DIR__ . '/../components/___button_unfollow.php';
    echo "</mixhtml>";
} catch (Exception $e) {
    $api = $api ?? new class extends BaseApiController {};
    $api->serverError(["error" => $e->getMessage()]);
}
