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

    public function like(string $userPk, string $postPk): void {
        $this->ensureDb();
        global $_db;

        $checkSql = "SELECT 1 FROM likes WHERE like_user_fk = :userPk AND like_post_fk = :postPk";
        $checkStmt = $_db->prepare($checkSql);
        $checkStmt->bindValue(':userPk', $userPk);
        $checkStmt->bindValue(':postPk', $postPk);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn()) {
            throw new Exception("Post already liked", 400);
        }

        $sql = "INSERT INTO likes (like_user_fk, like_post_fk) VALUES (:userPk, :postPk)";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':postPk', $postPk);
        if (!$stmt->execute() || $stmt->rowCount() < 1) {
            throw new Exception("Could not like post", 500);
        }
    }

    public function unlike(string $userPk, string $postPk): void {
        $this->ensureDb();
        global $_db;
        $sql = "DELETE FROM likes WHERE like_user_fk = :userPk AND like_post_fk = :postPk";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':postPk', $postPk);
        if (!$stmt->execute()) {
            throw new Exception("Could not unlike post", 500);
        }
    }
}
