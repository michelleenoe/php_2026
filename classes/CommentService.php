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

        // Get the original post PK if this is a repost
        $originalPostPk = $this->getOriginalPostPk($postPk);

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
        $stmt->bindValue(':postPk', $originalPostPk);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentsWithOriginal(string $targetPk): array {
        $db = $this->db();

        // Get the original post PK if this is a repost
        $originalPostPk = $this->getOriginalPostPk($targetPk);
        
        $postPks = [$originalPostPk];

        $postPks = array_values(array_unique(array_filter($postPks)));
        if (!$postPks) return [];

        $placeholders = implode(',', array_fill(0, count($postPks), '?'));

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
            WHERE comments.comment_post_fk IN ($placeholders)
              AND comments.deleted_at IS NULL
              AND users.deleted_at IS NULL
            ORDER BY comments.comment_created_at ASC
        ";

        $stmt = $db->prepare($sql);
        foreach ($postPks as $idx => $pk) {
            $stmt->bindValue($idx + 1, $pk);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getOriginalPostPk($targetPk) {
        $db = $this->db();
        
        // If this is a repost, get the original post
        $stmt = $db->prepare("
            SELECT COALESCE(repost_post_pk, repost_like_pk, repost_comment_pk) as original_pk
            FROM reposts 
            WHERE repost_pk = :targetPk
        ");
        $stmt->bindValue(':targetPk', $targetPk);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['original_pk'])) {
            return $result['original_pk'];
        }
        
        // If not a repost, return the original target
        return $targetPk;
    }
}
