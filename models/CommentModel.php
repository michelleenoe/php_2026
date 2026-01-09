<?php

class CommentModel
{
    private function ensureDb()
    {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require_once __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function countForPost(string $postPk): int
    {
        $this->ensureDb();
        global $_db;

        $sql = 'SELECT COUNT(*) 
                FROM comments 
                WHERE comment_post_fk = :post_pk 
                AND deleted_at IS NULL';

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':post_pk', $postPk);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getByPost(string $postPk, ?int $limit = null, ?int $offset = null): array
    {
        $this->ensureDb();
        global $_db;

        $sql = "
            SELECT c.*, u.user_full_name, u.user_handle
            FROM comments c
            LEFT JOIN users u ON u.user_pk = c.comment_user_fk
            WHERE c.comment_post_fk = :post_pk
              AND c.deleted_at IS NULL
              AND (u.deleted_at IS NULL OR u.user_pk IS NULL)
            ORDER BY c.comment_created_at DESC
        ";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }
        if ($offset !== null) {
            $sql .= " OFFSET :offset";
        }

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':post_pk', $postPk);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}