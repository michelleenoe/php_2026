<?php
;

class NotificationService {
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function getForUser(string $userPk): array {
        $this->ensureDb();
        global $_db;
        $q = "
            SELECT
                n.notification_pk,
                n.notification_message,
                n.is_read,
                n.created_at,
                n.notification_post_fk,
                u.user_full_name,
                u.user_username,
                u.user_avatar,
                u.user_pk AS actor_pk
            FROM notifications n
            JOIN users u ON n.notification_actor_fk = u.user_pk
            WHERE n.notification_user_fk = :user
              AND u.deleted_at IS NULL
            ORDER BY n.created_at DESC
        ";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markRead(string $notificationPk, string $userPk): int {
        $this->ensureDb();
        global $_db;
        $q = "UPDATE notifications SET is_read = 1 WHERE notification_pk = :pk AND notification_user_fk = :user";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':pk', $notificationPk);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function markAllRead(string $userPk): int {
        $this->ensureDb();
        global $_db;
        $q = "UPDATE notifications SET is_read = 1 WHERE notification_user_fk = :user AND is_read = 0";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete(string $notificationPk, string $userPk): int {
        $this->ensureDb();
        global $_db;
        $q = "DELETE FROM notifications WHERE notification_pk = :pk AND notification_user_fk = :user";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':pk', $notificationPk);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function countUnread(string $userPk): int {
        $this->ensureDb();
        global $_db;
        $q = "
            SELECT COUNT(*)
            FROM notifications n
            JOIN users a ON a.user_pk = n.notification_actor_fk
            WHERE n.notification_user_fk = :user
              AND n.is_read = 0
              AND (n.deleted_at IS NULL OR n.deleted_at = '0000-00-00 00:00:00')
              AND a.deleted_at IS NULL
        ";
        $stmt = $_db->prepare($q);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
