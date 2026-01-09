<?php

class User {

    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require_once __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function getUser() {
        $this->ensureDb();
        global $_db;

        $sql = 'SELECT * FROM users LIMIT 1';
        $stmt = $_db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsername() {
        $this->ensureDb();
        global $_db;

        $sql = 'SELECT user_username FROM users LIMIT 1';
        $stmt = $_db->prepare($sql);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user['user_username'] ?? null;
    }
}