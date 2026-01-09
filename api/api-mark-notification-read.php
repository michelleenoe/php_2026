<?php
require_once __DIR__ . '/../x.php';
$db = _db();
header('Content-Type: application/json; charset=utf-8');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../controllers/BaseApiController.php';
require_once __DIR__ . '/../classes/NotificationService.php';

$api = new class extends BaseApiController {};

if (!isset($_SESSION['user'])){
    $api->unauthorized(['success'=>false,'message'=>'Not authenticated']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    $api->badRequest(['success'=>false,'message'=>'Invalid method']);
}
$pk = $_POST['notification_pk'] ?? null;
if (!$pk){
    $api->badRequest(['success'=>false,'message'=>'Missing notification_pk']);
}
require_once __DIR__ . '/../db.php';
try{
    $ns = new NotificationService();
    $rows = $ns->markRead($pk, $_SESSION['user']['user_pk']);
    $api->json(['success'=>true,'updated'=>$rows]);
}catch(Exception $e){
    $api->serverError(['success'=>false,'message'=>$e->getMessage()]);
}
