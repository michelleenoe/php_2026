<?php
session_start();
require_once __DIR__ . '/../x.php';

_ensureLogin('/');
$db = _db();
try {
    $commentTargetPk = _validatePk('post_pk');
    $commentMessage = _validateComment();

    $q = "
        SELECT 1 FROM posts WHERE post_pk = :pk AND deleted_at IS NULL
        UNION ALL
        SELECT 1 FROM reposts r
        JOIN posts p ON p.post_pk = r.repost_post_fk AND p.deleted_at IS NULL
        WHERE r.repost_pk = :pk
        LIMIT 1
    ";
    $stmt = $db->prepare($q);
    $stmt->bindValue(':pk', $commentTargetPk);
    $stmt->execute();
    if (!$stmt->fetchColumn()) {
        throw new Exception("Post not found", 404);
    }

    $commentPk = bin2hex(random_bytes(25));

    $sql = "
      INSERT INTO comments
        (comment_pk, comment_post_fk, comment_user_fk, comment_message)
      VALUES
        (:comment_pk, :post_pk, :user_pk, :message)
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':comment_pk', $commentPk);
    $stmt->bindValue(':post_pk',   $commentTargetPk);
    $stmt->bindValue(':user_pk',   $_SESSION["user"]["user_pk"]);
    $stmt->bindValue(':message',   $commentMessage);

    if (!$stmt->execute() || $stmt->rowCount() !== 1) {
        $info = $stmt->errorInfo();
        throw new Exception("Could not create comment: " . ($info[2] ?? 'DB error'), 500);
    }


    $q = "
        SELECT c.comment_pk,
               c.comment_message,
               c.comment_created_at,
               c.updated_at,
               c.comment_user_fk,
               u.user_full_name,
               u.user_username,
               u.user_avatar
        FROM comments c
        LEFT JOIN users u ON u.user_pk = c.comment_user_fk
        WHERE c.comment_pk = :comment_pk
        LIMIT 1
    ";
    $stmt = $db->prepare($q);
    $stmt->bindValue(':comment_pk', $commentPk);
    $stmt->execute();
    $inserted = $stmt->fetch();

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => $inserted
    ]);
} catch (Exception $e) {
    error_log("[api-create-comment] " . $e->getMessage());
    try { _toastError($e->getMessage()); } catch (Exception $_) {}

    http_response_code($e->getCode() >= 400 ? $e->getCode() : 400);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'message' => $e->getMessage()
    ]);
}
