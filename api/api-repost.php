<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
_ensureLogin('/');

$redirect = _redirectPath('/home');

try {
    require_once __DIR__ . '/../db.php';

    // Determine what type of content is being reposted
    $postPk = $_GET['post-pk'] ?? $_GET['post_pk'] ?? null;
    $likePk = $_GET['like-pk'] ?? $_GET['like_pk'] ?? null;
    $commentPk = $_GET['comment-pk'] ?? $_GET['comment_pk'] ?? null;
    
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    
    // Validate that exactly one of the PKs is provided
    $providedParams = array_filter([$postPk, $likePk, $commentPk]);
    if (count($providedParams) !== 1) {
        throw new Exception('Exactly one of post-pk, like-pk, or comment-pk is required', 400);
    }
    
    $userPk = $_SESSION['user']['user_pk'];
    
    // Determine the target and validate it exists
    $targetType = '';
    $targetPk = '';
    $targetOwnerPk = '';
    
    if ($postPk) {
        $targetType = 'post';
        $targetPk = $postPk;
        $postStmt = $_db->prepare("SELECT post_pk, post_user_fk FROM posts WHERE post_pk = :pk AND deleted_at IS NULL");
        $postStmt->bindValue(':pk', $postPk);
        $postStmt->execute();
        $postRow = $postStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$postRow) {
            throw new Exception('Post not found', 404);
        }
        
        $targetOwnerPk = $postRow['post_user_fk'];
    } elseif ($likePk) {
        $targetType = 'like';
        $targetPk = $likePk;
        $likeStmt = $_db->prepare("SELECT like_pk, like_user_fk, like_post_fk FROM likes WHERE like_pk = :pk");
        $likeStmt->bindValue(':pk', $likePk);
        $likeStmt->execute();
        $likeRow = $likeStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$likeRow) {
            throw new Exception('Like not found', 404);
        }
        
        // Get the post owner for notification purposes
        $postStmt = $_db->prepare("SELECT post_user_fk FROM posts WHERE post_pk = :post_pk AND deleted_at IS NULL");
        $postStmt->bindValue(':post_pk', $likeRow['like_post_fk']);
        $postStmt->execute();
        $postRow = $postStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$postRow) {
            throw new Exception('Associated post not found', 404);
        }
        
        $targetOwnerPk = $postRow['post_user_fk'];
    } elseif ($commentPk) {
        $targetType = 'comment';
        $targetPk = $commentPk;
        $commentStmt = $_db->prepare("SELECT comment_pk, comment_user_fk, comment_post_fk FROM comments WHERE comment_pk = :pk AND deleted_at IS NULL");
        $commentStmt->bindValue(':pk', $commentPk);
        $commentStmt->execute();
        $commentRow = $commentStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$commentRow) {
            throw new Exception('Comment not found', 404);
        }
        
        // Get the post owner for notification purposes
        $postStmt = $_db->prepare("SELECT post_user_fk FROM posts WHERE post_pk = :post_pk AND deleted_at IS NULL");
        $postStmt->bindValue(':post_pk', $commentRow['comment_post_fk']);
        $postStmt->execute();
        $postRow = $postStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$postRow) {
            throw new Exception('Associated post not found', 404);
        }
        
        $targetOwnerPk = $postRow['post_user_fk'];
    }

    // Check if user already reposted this item
    $checkQuery = "SELECT repost_pk FROM reposts WHERE repost_user_fk = :user AND ";
    $params = [':user' => $userPk];
    
    switch ($targetType) {
        case 'post':
            $checkQuery .= "repost_post_pk = :target";
            $params[':target'] = $targetPk;
            break;
        case 'like':
            $checkQuery .= "repost_like_pk = :target";
            $params[':target'] = $targetPk;
            break;
        case 'comment':
            $checkQuery .= "repost_comment_pk = :target";
            $params[':target'] = $targetPk;
            break;
    }
    
    $check = $_db->prepare($checkQuery);
    $check->execute($params);

    if ($existing = $check->fetch(PDO::FETCH_ASSOC)) {
        $delete = $_db->prepare("DELETE FROM reposts WHERE repost_pk = :pk");
        $delete->bindValue(':pk', $existing['repost_pk']);
        $delete->execute();

        if ($isAjax) {
            _toastOk('Repost removed');
            echo "Repost removed";
            exit;
        } else {
            _toastOk('Repost removed');
            echo "<mixhtml mix-redirect=\"{$redirect}\"></mixhtml>";
            exit;
        }
    } else {
        // Insert new repost record
        $insertQuery = "INSERT INTO reposts (repost_pk, repost_user_fk, ";
        
        switch ($targetType) {
            case 'post':
                $insertQuery .= "repost_post_pk, created_at) VALUES (:pk, :user, :target, NOW())";
                break;
            case 'like':
                $insertQuery .= "repost_like_pk, created_at) VALUES (:pk, :user, :target, NOW())";
                break;
            case 'comment':
                $insertQuery .= "repost_comment_pk, created_at) VALUES (:pk, :user, :target, NOW())";
                break;
        }
        
        $insert = $_db->prepare($insertQuery);
        $insert->execute([
            ':pk'   => bin2hex(random_bytes(25)),
            ':user' => $userPk,
            ':target' => $targetPk
        ]);

        // Create notification if the target owner is not the current user
        if (!empty($targetOwnerPk) && $targetOwnerPk !== $userPk) {
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notif = new NotificationModel();
            $actorUsername = $_SESSION['user']['user_username'] ?? 'Someone';
            
            $message = '';
            switch ($targetType) {
                case 'post':
                    $message = "$actorUsername reposted your post";
                    break;
                case 'like':
                    $message = "$actorUsername reposted your like";
                    break;
                case 'comment':
                    $message = "$actorUsername reposted your comment";
                    break;
            }
            
            $notif->createForUser(
                $targetOwnerPk,
                $userPk,
                $targetPk,  // This could be the original post pk for the notification link
                $message
            );
        }

        if ($isAjax) {
            _toastOk('Content reposted');
            echo "Content reposted";
            exit;
        } else {
            _toastOk('Content reposted');
            echo "<mixhtml mix-redirect=\"{$redirect}\"></mixhtml>";
            exit;
        }
    }
} catch (Exception $e) {
    if ($isAjax) {
        _toastError($e->getMessage());
        http_response_code($e->getCode() ?: 500);
        echo $e->getMessage();
        exit;
    } else {
        _toastError($e->getMessage());
        echo "<mixhtml mix-redirect=\"{$redirect}\"></mixhtml>";
        exit;
    }
}
