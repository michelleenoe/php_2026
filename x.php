<?php
function weaveIsProd() {
    $host = $_SERVER["HTTP_HOST"] ?? '';
    return in_array($host, [
        'michelleenoe.com',
        'www.michelleenoe.com'
    ]);
}

if (!function_exists('_')) {
    function _($text) {
        echo htmlspecialchars($text);
    }
}

function _noCache(){
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Clear-Site-Data: "cache", "cookies", "storage", "executionContexts"');
}

define("postMinLength", 1);
define("postMaxLength", 200);
function _validatePost() {
    $postMessage = trim($_POST['post_message']);
    $len = strlen($postMessage);
    if ($len < postMinLength || $len > postMaxLength) {
        throw new Exception("Post cannot be empty, must be at least ".postMinLength." characters long", 400);
    }
    return $postMessage;
}

define("emailMin", 6);
define("emailMax", 50);
function _validateEmail(){
    $userEmail = trim($_POST["user_email"]);
    if(strlen($userEmail) < emailMin){
        _toastError("Email must be at least ".emailMin." characters long");
        throw new Exception("Email must be at least ".emailMin." characters long", 400);
    }
    if(strlen($userEmail) > emailMax){
        _toastError("Email must be max ".emailMax." characters long");
        throw new Exception("Email must be max ".emailMax." characters long", 400);
    }
    if(!preg_match("/".REGEX_EMAIL."/", $userEmail)){
        _toastError("Please enter a valid email address");
        throw new Exception("Please enter a valid email address", 400);
    }

    return $userEmail;
}

define("passwordMin", 6);
define("passwordMax", 50);
function _validatePassword(){
    $userPassword = trim($_POST["user_password"]);
    if(strlen($userPassword) < passwordMin){
        _toastError("Password must be at least ".passwordMin." characters long");
        throw new Exception("Password must be at least ".passwordMin." characters long", 400);
    }
    if(strlen($userPassword) > passwordMax){
        _toastError("Password must be max ".passwordMax." characters long");
        throw new Exception("Password must be max ".passwordMax." characters long", 400);
    }
    return $userPassword;
}

define("userFullNameMin", 1);
define("userFullNameMax", 20);
function _validateUserFullName(){
    $userFullName = trim($_POST["user_full_name"]);
    if(strlen($userFullName) < userFullNameMin){
        throw new Exception("Full name must be at least ".userFullNameMin." characters long", 400);
    }
    if(strlen($userFullName) > userFullNameMax){
        throw new Exception("Full name must be max ".userFullNameMax." characters long", 400);
    }
    return $userFullName;
}

define("usernameMin", 2);
define("usernameMax", 20);
function _validateUsername(){
    $username = trim($_POST["user_username"]);
    if(strlen($username) < usernameMin){
        throw new Exception("Username must be at least ".usernameMin." characters long", 400);
    }
    if(strlen($username) > usernameMax){
        throw new Exception("Username must be max ".usernameMax." characters long", 400);
    }
    return $username;
}

define("REGEX_FULLNAME", "^.{".userFullNameMin.",".userFullNameMax."}$");
define("REGEX_USERNAME", "^.{".usernameMin.",".usernameMax."}$");
define("REGEX_EMAIL", "^[^\\s@]+@[^\\s@]+\\.[^\\s@]{2,}$");
define("REGEX_PASSWORD", "^.{".passwordMin.",".passwordMax."}$");

define("pkMinLength", 1);
define("pkMaxLength", 50);
function _validatePk($fieldName) {
    $pk = trim($_POST[$fieldName]);
    $len = strlen($pk);
    if ($len < pkMinLength) {
        throw new Exception("Primary key must be at least " . pkMinLength . " characters");
    } else if ($len > pkMaxLength) {
        throw new Exception("Primary key must be at most " . pkMaxLength . " characters");
    }
    return $pk;
}

define("commentMinLength", 1);
define("commentMaxLength", 200);
function _validateComment($text = null){
    if ($text === null) {
        $text = $_POST['comment_message'] ?? '';
    }
    $comment = trim((string)$text);
    $len = strlen($comment);
    if ($len < commentMinLength) {
        throw new Exception("Comment cannot be empty, must be at least " . commentMinLength . " characters long", 400);
    }
    if ($len > commentMaxLength) {
        throw new Exception("Comment must be at most " . commentMaxLength . " characters long", 400);
    }
    return $comment;
}

function _redirectPath(string $fallback = '/home'): string {
    if (!empty($_POST['redirect_to'])) {
        $redirect = $_POST['redirect_to'];
    } elseif (!empty($_GET['redirect_to'])) {
        $redirect = $_GET['redirect_to'];
    } elseif (!empty($_SERVER['HTTP_REFERER'])) {
        $redirect = $_SERVER['HTTP_REFERER'];
    } else {
        $redirect = $fallback;
    }
    $parsed = parse_url($redirect);
    $path   = $parsed['path'] ?? $fallback;
    $query  = isset($parsed['query']) ? ('?' . $parsed['query']) : '';
    $redirect = $path . $query;
    if (strpos($redirect, '/') !== 0) {
        $redirect = $fallback;
    }
    return $redirect;
}

function _setToast(string $message, string $type = 'ok'): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['toast'] = [
        'message' => $message,
        'type'    => $type
    ];
}

function _toastOk(string $message): void {
    _setToast($message, 'ok');
}

function _toastError(string $message): void {
    _setToast($message, 'error');
}

function _toastRedirect(string $message, string $type, string $location): void {
    _setToast($message, $type);
    header("Location: $location");
    exit();
}

function _ensureLogin(string $redirect = '/'): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        _toastError('Not logged in, please login first');
        header("Location: $redirect");
        exit();
    }
}

function _currentUser(): ?array {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return $_SESSION['user'] ?? null;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendWeaveMail($to, $subject, $body) {
    $host = $_SERVER["HTTP_HOST"] ?? '';
    $isProd =
        $host === "michelleenoe.com" ||
        $host === "www.michelleenoe.com";

    if (!$isProd) {
        file_put_contents(__DIR__ . "/../mail-log.txt",
            "[" . date("Y-m-d H:i:s") . "] LOCAL MAIL SKIPPED → $to : $subject\n",
            FILE_APPEND
        );
        return true;
    }

    $autoloadCandidates = [
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/../vendor/autoload.php',
    ];
    $autoloadLoaded = false;
    foreach ($autoloadCandidates as $path) {
        if (file_exists($path)) {
            require_once $path;
            $autoloadLoaded = true;
            break;
        }
    }
    if (!$autoloadLoaded) {
        file_put_contents(__DIR__ . "/../mail-errors.txt",
            "[" . date("Y-m-d H:i:s") . "] MAIL ERROR → vendor/autoload.php not found\n",
            FILE_APPEND
        );
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $smtpHost = getenv('SMTP_HOST') ?: '';
        $smtpUser = getenv('SMTP_USER') ?: '';
        $smtpPass = getenv('SMTP_PASS') ?: '';
        $smtpPort = (int) (getenv('SMTP_PORT') ?: 587);

        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->Port       = $smtpPort;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($smtpUser, 'Weave');
        $mail->addAddress($to);

        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->isHTML(false);

        return $mail->send();
    } catch (Exception $e) {
        file_put_contents(__DIR__ . "/../mail-errors.txt",
            "[" . date("Y-m-d H:i:s") . "] MAIL ERROR → " . $e->getMessage() . "\n",
            FILE_APPEND
        );
        return false;
    }
}
function _db() {
    global $_db;

    if ($_db instanceof PDO) {
        return $_db;
    }

    require_once __DIR__ . '/db.php';

    if (!($_db instanceof PDO)) {
        throw new Exception("Database connection unavailable", 500);
    }

    return $_db;
}