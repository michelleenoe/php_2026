<?php

class SearchModel {

    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function searchUsers($term) {
        $this->ensureDb();
        global $_db;

        $sql = "
            SELECT 
                user_pk,
                user_username,
                user_full_name,
                user_email
            FROM users
            WHERE 
                (user_username LIKE :q
                OR user_full_name LIKE :q
                OR user_email LIKE :q)
                AND deleted_at IS NULL
            LIMIT 25
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':q', "%$term%");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchPosts($term) {
        $this->ensureDb();
        global $_db;

        $sql = "
            SELECT 
                posts.post_pk,
                posts.post_message,
                posts.post_image_path,
                posts.post_user_fk,
                users.user_username,
                users.user_full_name,
                users.user_avatar
            FROM posts
            JOIN users ON posts.post_user_fk = users.user_pk
            WHERE posts.post_message LIKE :q
            AND posts.deleted_at IS NULL
            AND users.deleted_at IS NULL
            LIMIT 25
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':q', "%$term%");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchHashtag($tag) {
        $this->ensureDb();
        global $_db;

        $sql = "
            SELECT 
                posts.post_pk,
                posts.post_message,
                posts.post_image_path,
                posts.post_user_fk,
                users.user_username,
                users.user_full_name,
                users.user_avatar
            FROM posts
            JOIN users ON posts.post_user_fk = users.user_pk
            WHERE posts.post_message REGEXP :tag
            AND posts.deleted_at IS NULL
            AND users.deleted_at IS NULL
            LIMIT 50
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':tag', "#$tag");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}