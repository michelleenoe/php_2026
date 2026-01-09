<?php
require_once __DIR__ . '/../db.php';

class UserService {
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function getActiveUserByPk(string $userPk): ?array {
        $this->ensureDb();
        global $_db;
        $q = "SELECT * FROM users WHERE user_pk = :userPk AND deleted_at IS NULL LIMIT 1";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function deleteProfileCascade(string $userPk): void {
        $this->ensureDb();
        global $_db;

        $sqls = [
            "UPDATE users SET deleted_at = NOW() WHERE user_pk = :user_pk AND deleted_at IS NULL",
            "UPDATE posts SET deleted_at = NOW() WHERE post_user_fk = :user_pk AND deleted_at IS NULL",
            "UPDATE comments SET deleted_at = NOW() WHERE comment_user_fk = :user_pk AND deleted_at IS NULL",
            "DELETE FROM likes WHERE like_user_fk = :user_pk",
            "DELETE FROM follows WHERE follow_user_fk = :user_pk OR follower_user_fk = :user_pk"
        ];

        foreach ($sqls as $sql) {
            $stmt = $_db->prepare($sql);
            $stmt->bindValue(":user_pk", $userPk);
            $stmt->execute();
        }
    }
}
