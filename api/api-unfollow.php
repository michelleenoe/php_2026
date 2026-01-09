<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
if (!isset($_SESSION["user"])) {
    require_once __DIR__ . '/../controllers/BaseApiController.php';
    $api = new class extends BaseApiController {};
    $api->unauthorized(["error" => "Please login to unfollow a user"]);
}

try {
    require_once __DIR__ . '/../classes/FollowService.php';
    require_once __DIR__ . '/../controllers/BaseApiController.php';
    $api = $api ?? new class extends BaseApiController {};
    $followerPk = $_SESSION["user"]["user_pk"];
    $followPk = $_GET['user-pk'];

    $followService = new FollowService();
    $followService->unfollow($followerPk, $followPk);

    $user_pk = $followPk;
    echo "<mixhtml mix-replace='.button-$user_pk'>";
    require __DIR__ . '/../components/___button_follow.php';
    echo "</mixhtml>";
} catch (Exception $e) {
    $api = $api ?? new class extends BaseApiController {};
    $api->serverError(["error" => $e->getMessage()]);
}
