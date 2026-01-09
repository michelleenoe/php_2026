<?php
session_start();
require_once __DIR__ . '/../x.php';

try {
$db = _db();

$token = $_GET["token"] ?? null;

if (!$token) {
    _toastError("Missing verification token.");
    header("Location: /");
    exit();
}

$stmt = $db->prepare("
    SELECT user_pk 
    FROM users 
    WHERE user_verify_token = :t 
      AND user_is_verified = 0
    LIMIT 1
");
$stmt->execute([':t' => $token]);
$userPk = $stmt->fetchColumn();

if (!$userPk) {
    _toastError("Invalid or expired verification link.");
    header("Location: /");
    exit();
}

$stmt = $db->prepare("
    UPDATE users
    SET user_is_verified = 1,
        user_verify_token = NULL
    WHERE user_pk = :pk
");
$stmt->execute([':pk' => $userPk]);
_toastOk("Your email is verified! You may now log in.");
$_SESSION['open_dialog'] = 'login';
header("Location: /");
exit();
} catch (Exception $e) {
    _toastError($e->getMessage());
    header("Location: /");
}
