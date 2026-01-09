<?php

class CommentService {

    private function db(): PDO {
        return _db();
    }

    public function updateComment(string $commentPk, string $message, string $userPk): void {
        $db = $this->db();

        $checkSql = "
          SELECT 1
          FROM comments
          WHERE comment_pk = :commentPk
            AND comment_user_fk = :userPk
            AND deleted_at IS NULL
        ";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            ':commentPk' => $commentPk,
            ':userPk'    => $userPk
        ]);

        if (!$checkStmt->fetchColumn()) {
            throw new Exception("You do not have permission to update this comment.", 403);
        }

        $sql = "
            UPDATE comments
            SET comment_message = :message,
                updated_at = NOW()
            WHERE comment_pk = :commentPk
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':commentPk', $commentPk);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception("Comment was not updated", 400);
        }
    }

    public function deleteComment(string $commentPk, string $userPk): void {
        $db = $this->db();

        $checkSql = "
            SELECT 1
            FROM comments
            WHERE comment_pk = :commentPk
              AND comment_user_fk = :userPk
              AND deleted_at IS NULL
        ";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            ':commentPk' => $commentPk,
            ':userPk'    => $userPk
        ]);

        if (!$checkStmt->fetchColumn()) {
            throw new Exception("You do not have permission to delete this comment.", 403);
        }

        $sql = "
            UPDATE comments
            SET deleted_at = NOW()
            WHERE comment_pk = :commentPk
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':commentPk', $commentPk);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception("Comment was not deleted", 400);
        }
    }

    public function getCommentsByPost(string $postPk): array {
        $db = $this->db();

        $sql = "
            SELECT
                comments.comment_pk,
                comments.comment_message,
                comments.comment_created_at,
                comments.updated_at,
                comments.comment_user_fk,
                users.user_full_name,
                users.user_username,
                users.user_avatar
            FROM comments
            JOIN users ON comments.comment_user_fk = users.user_pk
            WHERE comments.comment_post_fk = :postPk
              AND comments.deleted_at IS NULL
              AND users.deleted_at IS NULL
            ORDER BY comments.comment_created_at ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':postPk', $postPk);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentsByTarget(string $targetPk, string $targetType = 'post'): array {
        $db = $this->db();

        $sql = "
            SELECT
                comments.comment_pk,
                comments.comment_message,
                comments.comment_created_at,
                comments.updated_at,
                comments.comment_user_fk,
                users.user_full_name,
                users.user_username,
                users.user_avatar
            FROM comments
            JOIN users ON comments.comment_user_fk = users.user_pk
            WHERE ";
        
        if ($targetType === 'post') {
            $sql .= "comments.comment_post_fk = :targetPk AND comments.comment_target_type = 'post'";
        } else if ($targetType === 'repost') {
            $sql .= "comments.comment_repost_fk = :targetPk AND comments.comment_target_type = 'repost'";
        } else {
            throw new Exception("Invalid target type: $targetType", 400);
        }
        
        $sql .= "
              AND comments.deleted_at IS NULL
              AND users.deleted_at IS NULL
            ORDER BY comments.comment_created_at ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':targetPk', $targetPk);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCommentToTarget(string $userPk, string $targetPk, string $message, string $targetType = 'post'): array {
        $db = $this->db();

        $commentPk = bin2hex(random_bytes(25));
        
        $sql = "INSERT INTO comments (comment_pk, comment_message, comment_user_fk, ";
        if ($targetType === 'post') {
            $sql .= "comment_post_fk, comment_target_type";
        } else if ($targetType === 'repost') {
            $sql .= "comment_repost_fk, comment_target_type";
        }
        $sql .= ", comment_created_at) VALUES (:commentPk, :message, :userPk, :targetPk, :targetType, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':commentPk', $commentPk);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':targetPk', $targetPk);
        $stmt->bindValue(':targetType', $targetType);
        
        if (!$stmt->execute()) {
            throw new Exception("Could not add comment", 500);
        }

        // Return the created comment
        $stmt = $db->prepare("
            SELECT
                comments.comment_pk,
                comments.comment_message,
                comments.comment_created_at,
                comments.updated_at,
                comments.comment_user_fk,
                users.user_full_name,
                users.user_username,
                users.user_avatar
            FROM comments
            JOIN users ON comments.comment_user_fk = users.user_pk
            WHERE comments.comment_pk = :commentPk
              AND users.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bindValue(':commentPk', $commentPk);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("Could not fetch created comment", 500);
        }
        
        return $result;
    }
}
