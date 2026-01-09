<?php
require_once __DIR__ . '/../x.php';
_ensureLogin('/');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/NotificationService.php';

$currentUser = _currentUser();
if (!$currentUser) {
    header('Location: /');
    exit();
}
$currentUserPk = $currentUser['user_pk'];
$notificationService = new NotificationService();
$notifications = $notificationService->getForUser($currentUserPk);

function timeAgo($ts){
    try{ $d = new DateTime($ts); }catch(Exception $e){ return ''; }
    $diff = (new DateTime())->getTimestamp() - $d->getTimestamp();
    if($diff < 60) return $diff . 's';
    if($diff < 3600) return floor($diff/60) . 'm';
    if($diff < 86400) return floor($diff/3600) . 'h';
    return floor($diff/86400) . 'd';
}
