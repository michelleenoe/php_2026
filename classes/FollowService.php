<?php
require_once __DIR__ . '/../models/FollowModel.php';
require_once __DIR__ . '/../db.php';

class FollowService {
    private FollowModel $followModel;
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function __construct(?FollowModel $followModel = null) {
        $this->followModel = $followModel ?? new FollowModel();
    }

    public function getSuggestions(string $currentUser, int $limit = 3, int $offset = 0): array {
        $this->ensureDb();
        return $this->followModel->getSuggestions($currentUser, $limit, $offset);
    }

    public function follow(string $followerPk, string $followPk): void {
        $this->ensureDb();
        global $_db;

        if ($followerPk === $followPk) {
            throw new Exception("You cannot follow yourself", 400);
        }

        $userSql = "SELECT user_pk FROM users WHERE user_pk = :followPk AND deleted_at IS NULL LIMIT 1";
        $userStmt = $_db->prepare($userSql);
        $userStmt->bindValue(':followPk', $followPk);
        $userStmt->execute();
        if (!$userStmt->fetchColumn()) {
            throw new Exception("User was deleted", 404);
        }

        $checkSql = "SELECT 1 FROM follows WHERE follower_user_fk = :followerPk AND follow_user_fk = :followPk";
        $checkStmt = $_db->prepare($checkSql);
        $checkStmt->bindParam(':followerPk', $followerPk);
        $checkStmt->bindParam(':followPk', $followPk);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn()) {
            throw new Exception("Already following this user", 400);
        }

        $sql = "INSERT INTO follows (follower_user_fk, follow_user_fk) VALUES (:followerPk, :followPk)";
        $stmt = $_db->prepare($sql);
        $stmt->bindParam(':followerPk', $followerPk);
        $stmt->bindParam(':followPk', $followPk);
        $stmt->execute();
    }

    public function unfollow(string $followerPk, string $followPk): void {
        $this->ensureDb();
        global $_db;
        $sql = "DELETE FROM follows WHERE follower_user_fk = :followerPk AND follow_user_fk = :followPk";
        $stmt = $_db->prepare($sql);
        $stmt->bindParam(':followerPk', $followerPk);
        $stmt->bindParam(':followPk', $followPk);
        $stmt->execute();
    }

    public function getFollowing(string $userPk, int $limit = 3): array {
        $this->ensureDb();
        global $_db;
        $sql = "
          SELECT users.*
          FROM follows
          JOIN users ON follows.follow_user_fk = users.user_pk
          WHERE follows.follower_user_fk = :userPk
            AND users.deleted_at IS NULL
          ORDER BY users.user_full_name ASC
          LIMIT :limit
        ";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFollowers(string $userPk, int $limit = 3): array {
        $this->ensureDb();
        global $_db;
        $sql = "
          SELECT users.*
          FROM follows
          JOIN users ON follows.follower_user_fk = users.user_pk
          WHERE follows.follow_user_fk = :userPk
            AND users.deleted_at IS NULL
          ORDER BY users.user_full_name ASC
          LIMIT :limit
        ";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFollowing(string $userPk): int {
        $this->ensureDb();
        global $_db;
        $sql = "SELECT COUNT(*) as total FROM follows WHERE follower_user_fk = :userPk";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countFollowers(string $userPk): int {
        $this->ensureDb();
        global $_db;
        $sql = "SELECT COUNT(*) as total FROM follows WHERE follow_user_fk = :userPk";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':userPk', $userPk);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function isFollowing(string $followerPk, string $followPk): bool {
        $this->ensureDb();
        global $_db;
        $q = "SELECT COUNT(*) FROM follows WHERE follower_user_fk = :followerPk AND follow_user_fk = :followPk";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':followerPk', $followerPk);
        $stmt->bindValue(':followPk', $followPk);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getFollowersPaged(string $userPk, int $offset, int $limit): array {
        $this->ensureDb();
        global $_db;
        $sql = "
            SELECT u.user_pk, u.user_full_name, u.user_username, u.user_avatar
            FROM follows f
            JOIN users u ON u.user_pk = f.follower_user_fk
            WHERE f.follow_user_fk = :user
              AND u.deleted_at IS NULL
            ORDER BY u.user_full_name ASC
            LIMIT :offset, :limit
        ";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':user', $userPk, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFollowingPaged(string $userPk, int $offset, int $limit): array {
        $this->ensureDb();
        global $_db;
        $sql = "
            SELECT u.user_pk, u.user_full_name, u.user_username, u.user_avatar
            FROM follows f
            JOIN users u ON u.user_pk = f.follow_user_fk
            WHERE f.follower_user_fk = :user
              AND u.deleted_at IS NULL
            ORDER BY u.user_full_name ASC
            LIMIT :offset, :limit
        ";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':user', $userPk, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
