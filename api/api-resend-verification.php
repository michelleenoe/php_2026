<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();

$email = $_POST["email"] ?? null;

if (!$email) {
    _toastError("Email missing");
    header("Location: /");
    exit();
}

$stmt = $db->prepare("SELECT * FROM users WHERE user_email = :email AND deleted_at IS NULL");
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    _toastError("User not found");
    header("Location: /");
    exit();
}

if (!empty($user["user_is_verified"])) {
    _toastOk("Your email is already verified.");
    header("Location: /");
    exit();
}

$token = bin2hex(random_bytes(32));

$stmt = $_db->prepare("
    UPDATE users
    SET user_verify_token = :token
    WHERE user_pk = :pk
");
$stmt->bindValue(':token', $token);
$stmt->bindValue(':pk', $user['user_pk']);
$stmt->execute();

if (!weaveIsProd()) {
    _toastOk("Verification skipped (local environment).");
    $_SESSION['open_dialog'] = null;
    header("Location: /home");
    exit();
}

$verifyUrl = "https://michelleenoe.com/verify-email?token=$token";

sendWeaveMail(
    $email,
    "Verify your email",
    "Click the link to verify your account: $verifyUrl"
);

_toastOk("Verification email resent.");
$_SESSION['open_dialog'] = 'signup_verified';

header("Location: /");
exit();