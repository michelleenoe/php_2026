<?php
require_once __DIR__ . '/../db.php';

class TrendingModel {
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function getTrending($limit = 4, $offset = 0) {
        $this->ensureDb();
        global $_db;

        $stmt = $_db->prepare("
            SELECT post_message
            FROM posts
            WHERE post_message REGEXP '#[A-Za-z0-9_]+'
        ");
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return array();

        $counts = array();

        foreach ($rows as $row) {
            preg_match_all('/#[A-Za-z0-9_]+/', $row['post_message'], $matches);

            foreach ($matches[0] as $tag) {
                $tagLower = strtolower($tag);
                if (!isset($counts[$tagLower])) {
                    $counts[$tagLower] = 0;
                }
                $counts[$tagLower]++;
            }
        }

        arsort($counts);

        $allTags = array_keys($counts);
        $slice = array_slice($allTags, $offset, $limit);

        $result = array();
        foreach ($slice as $tag) {
            $result[] = array(
                "tag" => $tag,
                "count" => $counts[$tag]
            );
        }

        return $result;
    }
}
