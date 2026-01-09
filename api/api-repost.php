<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
_ensureLogin('/');

$redirect = _redirectPath('/home');

try {
    require_once __DIR__ . '/../db.php';

    $postPk = $_GET['post-pk'] ?? $_GET['post_pk'] ?? null;
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    if (!$postPk) {
        throw new Exception('Post ID is required', 400);
    }
    $postStmt = $_db->prepare("SELECT post_pk, post_user_fk FROM posts WHERE post_pk = :pk AND deleted_at IS NULL");
    $postStmt->bindValue(':pk', $postPk);
    $postStmt->execute();
    $postRow = $postStmt->fetch(PDO::FETCH_ASSOC);

    if (!$postRow) {
        throw new Exception('Post not found', 404);
    }

    $userPk = $_SESSION['user']['user_pk'];

    $check = $_db->prepare("SELECT repost_pk FROM reposts WHERE repost_user_fk = :user AND repost_post_fk = :post LIMIT 1");
    $check->execute([
        ':user' => $userPk,
        ':post' => $postPk
    ]);

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
        $insert = $_db->prepare("INSERT INTO reposts (repost_pk, repost_user_fk, repost_post_fk, created_at) VALUES (:pk, :user, :post, NOW())");
        $insert->execute([
            ':pk'   => bin2hex(random_bytes(25)),
            ':user' => $userPk,
            ':post' => $postPk
        ]);

        if (!empty($postRow['post_user_fk']) && $postRow['post_user_fk'] !== $userPk) {
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notif = new NotificationModel();
            $actorUsername = $_SESSION['user']['user_username'] ?? 'Someone';
            $notif->createForUser(
                $postRow['post_user_fk'],
                $userPk,
                $postPk,
                "$actorUsername reposted your post"
            );
        }

        if ($isAjax) {
            _toastOk('Post reposted');
            echo "Post reposted";
            exit;
        } else {
            _toastOk('Post reposted');
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
