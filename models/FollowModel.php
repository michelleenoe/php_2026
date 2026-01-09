<?php
require_once __DIR__ . '/../db.php';

class FollowModel {
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function getSuggestions($currentUser, $limit = 3, $offset = 0) {
        $this->ensureDb();
        global $_db;

        $sql = "
            SELECT users.*
            FROM users
            WHERE users.user_pk != :user
              AND users.deleted_at IS NULL
              AND users.user_pk NOT IN (
                SELECT follow_user_fk
                FROM follows
                WHERE follower_user_fk = :user
              )
            ORDER BY users.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':user', $currentUser);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
