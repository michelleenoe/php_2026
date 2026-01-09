<?php

session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
_ensureLogin('/');

$isAjax =
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (!empty($_SERVER['HTTP_ACCEPT']) &&
        strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

try {

    $user = $_SESSION["user"];

    $newEmail = _validateEmail();
    $newUsername = _validateUsername();
    $newFullName = _validateUserFullName();

 
    $unchanged = (
        trim($newEmail) === trim($user['user_email']) &&
        trim($newUsername) === trim($user['user_username']) &&
        trim($newFullName) === trim($user['user_full_name'])
    );

    if ($unchanged) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Please change something before updating', 'error_code' => 'no_change']);
            exit();
        }
        _toastError('Please change something before updating');
        header('Location: /profile');
        exit();
    }


   
    $sqlCheck = "SELECT user_pk FROM users WHERE user_email = :email AND user_pk != :pk AND deleted_at IS NULL LIMIT 1";
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->bindValue(':email', $newEmail);
    $stmtCheck->bindValue(':pk', $user['user_pk']);
    $stmtCheck->execute();
    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email is already taken', 'error_code' => 'email_taken']);
            exit();
        }
        _toastError('Email is already taken');
        header('Location: /profile');
        exit();
    }
    

    $sqlCheckUser = "SELECT user_pk FROM users WHERE user_username = :username AND user_pk != :pk AND deleted_at IS NULL LIMIT 1";
    $stmtCheckUser = $db->prepare($sqlCheckUser);
    $stmtCheckUser->bindValue(':username', $newUsername);
    $stmtCheckUser->bindValue(':pk', $user['user_pk']);
    $stmtCheckUser->execute();
    $existingUser = $stmtCheckUser->fetch(PDO::FETCH_ASSOC);
    if ($existingUser) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Username is already taken', 'error_code' => 'username_taken']);
            exit();
        }
        _toastError('Username is already taken');
        header('Location: /profile');
        exit();
    }
    $sql = "UPDATE users SET user_email = :email, user_username = :username, user_full_name = :full_name, updated_at = NOW() WHERE user_pk = :pk AND deleted_at IS NULL";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $newEmail);
    $stmt->bindParam(':username', $newUsername);
    $stmt->bindParam(':full_name', $newFullName);
    $stmt->bindParam(':pk', $user['user_pk']);
    $stmt->execute();

    $user['user_email'] = $newEmail;
    $user['user_username'] = $newUsername;
    $user['user_full_name'] = $newFullName;
    $_SESSION["user"] = $user;

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Profile updated']);
        exit();
    }

    _toastRedirect('Profile updated', 'ok', '/profile');
    exit();
} catch (Exception $e) {

    $isDuplicateEmail = false;
    $isDuplicateUsername = false;
    if ($e instanceof PDOException) {
        $info = $e->errorInfo;
        if (is_array($info) && !empty($info[1]) && intval($info[1]) === 1062) {
          
            $msg = isset($info[2]) ? $info[2] : $e->getMessage();
            if (stripos($msg, 'user_email') !== false || stripos($msg, 'email') !== false) {
                $isDuplicateEmail = true;
            }
            if (stripos($msg, 'user_username') !== false || stripos($msg, 'username') !== false) {
                $isDuplicateUsername = true;
            }
        }
    } else {
    
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate entry') !== false) {
            if (stripos($msg, 'email') !== false || stripos($msg, 'user_email') !== false) {
                $isDuplicateEmail = true;
            }
            if (stripos($msg, 'username') !== false || stripos($msg, 'user_username') !== false) {
                $isDuplicateUsername = true;
            }
        }
    }

    if ($isDuplicateEmail) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email is already taken', 'error_code' => 'email_taken']);
            exit();
        }
        _toastError('Email is already taken');
        header('Location: /profile');
        exit();
    }

    if ($isDuplicateUsername) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Username is already taken', 'error_code' => 'username_taken']);
            exit();
        }
        _toastError('Username is already taken');
        header('Location: /profile');
        exit();
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not update profile']);
        exit();
    }

    _toastError('Could not update profile');
    header('Location: /profile');
    exit();
}
