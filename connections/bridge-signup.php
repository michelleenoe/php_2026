<?php
session_start();
require_once __DIR__ . '/../x.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/AuthService.php';

try {
    $userFullName = _validateUserFullName();
    $username     = _validateUsername();
    $userEmail    = _validateEmail();
    $userPassword = _validatePassword();
    
 
    $passwordConfirm = $_POST['user_password_confirm'] ?? '';
    if ($userPassword !== $passwordConfirm) {
        _toastError("Passwords do not match");
        $_SESSION['open_dialog'] = 'signup';
        header("Location: /");
        exit();
    }
    
    $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);
    $authService   = new AuthService();

    $existing = $authService->findExistingByUsernameOrEmail($_db, $username, $userEmail);

    if ($existing) {
        if (!empty($existing['user_username']) && $existing['user_username'] === $username) {
            _toastError("Username is already taken");
        } else {
            _toastError("An account already exists with this email");
        }
        $_SESSION['open_dialog'] = 'signup';
        header("Location: /");
        exit();
    }

    $userPk = bin2hex(random_bytes(25));
    $token  = bin2hex(random_bytes(32));

    $authService->registerUser($_db, $userPk, $username, $userFullName, $userEmail, $hashedPassword, $token);

    if (!weaveIsProd()) {
        $_db->prepare("
            UPDATE users 
            SET user_is_verified = 1,
                user_verify_token = NULL
            WHERE user_pk = :pk
        ")->execute([':pk' => $userPk]);

        $fetch = $_db->prepare("SELECT * FROM users WHERE user_pk = :pk LIMIT 1");
        $fetch->execute([':pk' => $userPk]);
        $newUser = $fetch->fetch();

        if ($newUser) {
            unset($newUser['user_password']);
            $_SESSION['user'] = $newUser;
        }

        _toastOk('Welcome!');
        header("Location: /home");
        exit();
    }

    $_SESSION['last_signup_email'] = $userEmail;

    $baseUrl = rtrim(getenv('APP_URL'), '/');
    $verifyUrl = "{$baseUrl}/api-verify-email?token={$token}";

    sendWeaveMail(
        $userEmail,
        "Verify your Weave account",
        "Click the link to verify your email: $verifyUrl"
    );

    _toastOk("We just sent you a verification email. Please check your inbox.");
    $_SESSION['open_dialog'] = 'signup_verified';

    header("Location: /");
    exit();

} catch (Exception $e) {
    _toastError($e->getMessage());
    $_SESSION['open_dialog'] = 'signup';

    $_SESSION['signup_old'] = $_POST;
    $_SESSION['signup_error_field'] = $e->getMessage();

    header("Location: /");
    exit();
}
