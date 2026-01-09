<?php
session_start();
require_once __DIR__.'/../x.php';
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../classes/AuthService.php';

try {
    $email    = _validateEmail();
    $password = _validatePassword();

    $authService = new AuthService();
    $user = $authService->authenticate($_db, $email, $password);
    
    if (!$user["user_is_verified"] && weaveIsProd()) {
        _toastError("Please verify your email before logging in");
        $_SESSION['open_dialog'] = 'login';
        header("Location: /");
        exit();
    }

    unset($user['user_password']);
    $_SESSION['user'] = $user;

    header("Location: /home");
    exit();

} catch (Exception $e) {
    _toastError($e->getMessage());
    $_SESSION['login_old'] = [
        'user_email' => $_POST['user_email'] ?? ''
    ];
    $_SESSION['login_error'] = $e->getMessage();
    $_SESSION['open_dialog'] = 'login';
    header("Location: /");
    exit();
}
