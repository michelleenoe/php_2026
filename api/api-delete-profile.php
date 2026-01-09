<?php

try {
    
    require_once __DIR__ . '/../x.php';
    $db = _db();
    require_once __DIR__ . '/../classes/UserService.php';


    $user = _currentUser();
    if (empty($user) || !isset($user['user_pk'])) {
        http_response_code(401);
        header("Location: /"); 
        
        exit();
    }

    $user_id = $user['user_pk'];

    $us = new UserService();
    $us->deleteProfileCascade($user_id);

    try {
        require_once __DIR__ . '/../models/NotificationModel.php';
        $nm = new NotificationModel();
        $nm->deleteForUser($user_id);
    } catch (Exception $_e) {
       
    }

    if (session_status() !== PHP_SESSION_NONE) {
        session_unset();
        session_destroy();
    }

    _toastRedirect('Profile was deleted', 'ok', '/');

} catch (Exception $e) {
    
    error_log("api-delete-profile error: " . $e->getMessage());

    if (function_exists('_toastRedirect')) {
        _toastRedirect('Could not delete profile', 'error', '/');
    } else {
        http_response_code(500);
        echo "error";
    }
}
