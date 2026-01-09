<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../controllers/BaseApiController.php';
require_once __DIR__ . '/../classes/NotificationService.php';
require_once __DIR__ . '/../x.php';
$db = _db();

$api = new class extends BaseApiController {};

if (!isset($_SESSION['user'])) {
    $api->unauthorized(["success" => false, "message" => "Not authenticated", "unread_count" => 0]);
}

require_once __DIR__ . '/../db.php';

try {
    $ns = new NotificationService();
    $count = $ns->countUnread($_SESSION['user']['user_pk']);

    $api->json(["success" => true, "unread_count" => $count]);
} catch (Exception $ex) {
    $api->serverError(["success" => false, "message" => $ex->getMessage(), "unread_count" => 0]);
}
