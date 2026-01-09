<?php
require_once __DIR__ . '/../db.php';

class LikeService {
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function like(string $userPk, string $targetPk, string $targetType = 'post'): void {
        $this->ensureDb();
        global $_db;

        // Check if already liked
        $checkSql = "SELECT 1 FROM likes WHERE like_user_fk = :userPk AND ";
        if ($targetType === 'post') {
            $checkSql .= "like_post_fk = :targetPk AND like_target_type = 'post'";
        } else if ($targetType === 'repost') {
            $checkSql .= "like_repost_fk = :targetPk AND like_target_type = 'repost'";
        } else {
            throw new Exception("Invalid target type: $targetType", 400);
        }
        
        $checkStmt = $_db->prepare($checkSql);
        $checkStmt->bindValue(':userPk', $userPk);
        $checkStmt->bindValue(':targetPk', $targetPk);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn()) {
            throw new Exception("Target already liked", 400);
        }

        // Insert the like with proper target
        $sql = "INSERT INTO likes (like_user_fk, ";
        if ($targetType === 'post') {
            $sql .= "like_post_fk, like_target_type";
        } else if ($targetType === 'repost') {
            $sql .= "like_repost_fk, like_target_type";
        }
        $sql .= ") VALUES (:userPk, :targetPk, :targetType)";
        
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':targetPk', $targetPk);
        $stmt->bindValue(':targetType', $targetType);
        
        if (!$stmt->execute() || $stmt->rowCount() < 1) {
            throw new Exception("Could not like target", 500);
        }
    }

    public function unlike(string $userPk, string $targetPk, string $targetType = 'post'): void {
        $this->ensureDb();
        global $_db;
        
        $sql = "DELETE FROM likes WHERE like_user_fk = :userPk AND ";
        if ($targetType === 'post') {
            $sql .= "like_post_fk = :targetPk AND like_target_type = 'post'";
        } else if ($targetType === 'repost') {
            $sql .= "like_repost_fk = :targetPk AND like_target_type = 'repost'";
        } else {
            throw new Exception("Invalid target type: $targetType", 400);
        }
        
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':targetPk', $targetPk);
        if (!$stmt->execute()) {
            throw new Exception("Could not unlike target", 500);
        }
    }
    
    public function getTargetPkAndType(string $postPk): array {
        // Check if this is a repost by looking for the original content in the reposts table
        $this->ensureDb();
        global $_db;
        
        // First check if the postPk is actually a repost_pk
        $checkRepost = $_db->prepare("SELECT repost_pk FROM reposts WHERE repost_pk = :pk LIMIT 1");
        $checkRepost->bindValue(':pk', $postPk);
        $checkRepost->execute();
        
        if ($checkRepost->fetchColumn()) {
            return ['pk' => $postPk, 'type' => 'repost'];
        }
        
        // Otherwise it's a regular post
        return ['pk' => $postPk, 'type' => 'post'];
    }
}
