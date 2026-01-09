<?php
require_once __DIR__ . '/../db.php';

class PostModel {
    private ?bool $repostsTableExists = null;
    private function ensureDb() {
        global $_db;
        if (!isset($_db) || !($_db instanceof PDO)) {
            require __DIR__ . '/../db.php';
        }
        return $_db;
    }

    public function getPostsForFeed($limit = 50, $currentUserPk = null, $offset = 0) {
        $this->ensureDb();
        global $_db;
        $currentUserPk = $this->normalizeUser($currentUserPk);
        $limit = (int) $limit;
        $offset = (int) $offset;

        $useReposts = $this->ensureRepostsAvailable();

        if (!$useReposts) {
            $sql = "
                SELECT 
                    p.post_pk,
                    p.post_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    NULL AS reposted_by,
                    NULL AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    p.created_at AS display_created_at
                FROM posts p
                JOIN users u ON u.user_pk = p.post_user_fk
                WHERE p.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                ORDER BY p.created_at DESC, p.post_pk DESC
                LIMIT :limit OFFSET :offset
            ";
        } else {
            $sql = "
            SELECT * FROM (
                SELECT 
                    p.post_pk,
                    p.post_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    NULL AS reposted_by,
                    NULL AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    p.created_at AS display_created_at
                FROM posts p
                JOIN users u ON u.user_pk = p.post_user_fk
                WHERE p.deleted_at IS NULL
                  AND u.deleted_at IS NULL

                UNION ALL

                SELECT 
                    p.post_pk,
                    r.repost_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    r.repost_user_fk AS reposted_by,
                    ur.user_username AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    r.created_at AS display_created_at
                FROM reposts r
                JOIN posts p ON p.post_pk = COALESCE(r.repost_post_pk, r.repost_like_pk, r.repost_comment_pk) AND p.deleted_at IS NULL
                JOIN users u ON u.user_pk = p.post_user_fk AND u.deleted_at IS NULL
                JOIN users ur ON ur.user_pk = r.repost_user_fk AND ur.deleted_at IS NULL
            ) feed
            ORDER BY feed.display_created_at DESC, feed.post_pk DESC
            LIMIT :limit OFFSET :offset
        ";
        }

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachMeta($posts, $currentUserPk);
    }

    public function getPostsLikedByUser(string $userPk, ?string $currentUserPk = null): array {
        $this->ensureDb();
        global $_db;
        $currentUserPk = $this->normalizeUser($currentUserPk);

        $sql = "
            SELECT 
                p.post_pk,
                p.post_pk AS comment_target_pk,
                p.post_message,
                p.post_image_path,
                p.post_user_fk,
                p.created_at,
                p.updated_at,
                u.user_username,
                u.user_full_name,
                u.user_avatar,
                NULL AS reposted_by,
                NULL AS reposted_by_username,
                p.post_pk AS root_post_pk,
                p.created_at AS display_created_at
            FROM likes l
            JOIN posts p ON p.post_pk = l.like_post_fk AND p.deleted_at IS NULL
            JOIN users u ON u.user_pk = p.post_user_fk AND u.deleted_at IS NULL
            WHERE l.like_user_fk = :user
            ORDER BY p.created_at DESC
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachMeta($posts, $currentUserPk);
    }


    public function getPostsByHashtag($tag, $currentUserPk = null) {
        $this->ensureDb();
        global $_db;
        $currentUserPk = $this->normalizeUser($currentUserPk);

        if (!$tag) return [];

        $escaped = preg_quote($tag, '/');
        $pattern = "(^|[^A-Za-z0-9_])#$escaped([^A-Za-z0-9_]|$)";

        $useReposts = $this->ensureRepostsAvailable();

        if (!$useReposts) {
            $sql = "
                SELECT 
                    p.post_pk,
                    p.post_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    NULL AS reposted_by,
                    NULL AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    p.created_at AS display_created_at
                FROM posts p
                JOIN users u ON u.user_pk = p.post_user_fk
                WHERE p.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                  AND p.post_message REGEXP :pattern
                ORDER BY p.created_at DESC
        ";
        } else {
            $sql = "
            SELECT * FROM (
                SELECT 
                    p.post_pk,
                    p.post_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    NULL AS reposted_by,
                    NULL AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    p.created_at AS display_created_at
                FROM posts p
                JOIN users u ON u.user_pk = p.post_user_fk
                WHERE p.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                  AND p.post_message REGEXP :pattern

                UNION ALL

                SELECT 
                    p.post_pk,
                    r.repost_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    r.repost_user_fk AS reposted_by,
                    ur.user_username AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    r.created_at AS display_created_at
                FROM reposts r
                JOIN posts p ON p.post_pk = COALESCE(r.repost_post_pk, r.repost_like_pk, r.repost_comment_pk) AND p.deleted_at IS NULL
                JOIN users u ON u.user_pk = p.post_user_fk AND u.deleted_at IS NULL
                JOIN users ur ON ur.user_pk = r.repost_user_fk AND ur.deleted_at IS NULL
                WHERE p.post_message REGEXP :pattern
            ) tagged
            ORDER BY tagged.display_created_at DESC
        ";
        }

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':pattern', $pattern);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachMeta($posts, $currentUserPk);
    }


    public function getPostById($postId, $currentUserPk = null) {
        $this->ensureDb();
        global $_db;
        $currentUserPk = $this->normalizeUser($currentUserPk);
        $sql = "
            SELECT 
                p.post_pk,
                p.post_pk AS comment_target_pk,
                p.post_message,
                p.post_image_path,
                p.post_user_fk,
                p.created_at,
                p.updated_at,
                u.user_username,
                u.user_full_name,
                u.user_avatar,
                NULL AS reposted_by,
                NULL AS reposted_by_username,
                p.post_pk AS root_post_pk,
                p.created_at AS display_created_at
            FROM posts p
            JOIN users u ON u.user_pk = p.post_user_fk
            WHERE p.deleted_at IS NULL
              AND p.post_pk = :id
              AND u.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(":id", $postId);
        $stmt->execute();

        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) return null;

        return $this->attachMeta([$post], $currentUserPk)[0];
    }

    public function getPostsByUser($userPk, $currentUserPk = null) {
        global $_db;
        $currentUserPk = $this->normalizeUser($currentUserPk);

        $sql = "
          SELECT
            p.post_pk,
            p.post_pk AS comment_target_pk,
            p.post_message,
            p.post_image_path,
            p.post_user_fk,
            p.created_at,
            p.updated_at,
            u.user_full_name,
            u.user_username,
            u.user_pk AS author_user_pk,
            u.user_avatar,
            NULL AS reposted_by,
            NULL AS reposted_by_username,
            p.post_pk AS root_post_pk,
            p.created_at AS display_created_at
          FROM posts p
          JOIN users u ON p.post_user_fk = u.user_pk
          WHERE p.post_user_fk = :userPk
            AND p.deleted_at IS NULL
            AND u.deleted_at IS NULL
          ORDER BY p.created_at DESC
        ";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(":userPk", $userPk);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->attachMeta($posts, $currentUserPk);
    }

    public function getPostsForUserWithReposts($userPk, $currentUserPk = null) {
        $this->ensureDb();
        global $_db;
        $currentUserPk = $this->normalizeUser($currentUserPk);

        $useReposts = $this->ensureRepostsAvailable();

        if (!$useReposts) {
            $sql = "
                SELECT 
                    p.post_pk,
                    p.post_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    NULL AS reposted_by,
                    NULL AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    p.created_at AS display_created_at
                FROM posts p
                JOIN users u ON u.user_pk = p.post_user_fk
                WHERE p.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                  AND p.post_user_fk = :user
                ORDER BY p.created_at DESC
            ";
        } else {
            $sql = "
            SELECT * FROM (
                SELECT 
                    p.post_pk,
                    p.post_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    NULL AS reposted_by,
                    NULL AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    p.created_at AS display_created_at
                FROM posts p
                JOIN users u ON u.user_pk = p.post_user_fk
                WHERE p.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                  AND p.post_user_fk = :user

                UNION ALL

                SELECT 
                    p.post_pk,
                    r.repost_pk AS comment_target_pk,
                    p.post_message,
                    p.post_image_path,
                    p.post_user_fk,
                    p.created_at,
                    p.updated_at,
                    u.user_username,
                    u.user_full_name,
                    u.user_avatar,
                    r.repost_user_fk AS reposted_by,
                    ur.user_username AS reposted_by_username,
                    p.post_pk AS root_post_pk,
                    r.created_at AS display_created_at
                FROM reposts r
                JOIN posts p ON p.post_pk = COALESCE(r.repost_post_pk, r.repost_like_pk, r.repost_comment_pk) AND p.deleted_at IS NULL
                JOIN users u ON u.user_pk = p.post_user_fk AND u.deleted_at IS NULL
                JOIN users ur ON ur.user_pk = r.repost_user_fk AND ur.deleted_at IS NULL
                WHERE r.repost_user_fk = :user
            ) user_posts
            ORDER BY user_posts.display_created_at DESC
        ";
        }

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':user', $userPk);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachMeta($posts, $currentUserPk);
    }


    public function createPost(string $postPk, string $message, string $imagePath, string $userPk): void {
        $this->ensureDb();
        global $_db;
        $sql = "INSERT INTO posts (post_pk, post_message, post_image_path, post_user_fk, created_at) Values (:post_pk, :post_message, :post_image_path, :post_user_fk, NOW())";

        $stmt = $_db->prepare($sql);
        $stmt->bindValue(":post_pk", $postPk);
        $stmt->bindValue(":post_message", $message);
        $stmt->bindValue(":post_image_path", $imagePath);
        $stmt->bindValue(":post_user_fk", $userPk);

        $stmt->execute();
    }

    public function updatePost(string $postPk, string $message, string $userPk): bool {
        $this->ensureDb();
        global $_db;
        $checkSql = "SELECT post_message FROM posts WHERE post_pk = :postPk AND post_user_fk = :userPk AND deleted_at IS NULL";
        $checkStmt = $_db->prepare($checkSql);
        $checkStmt->execute([':postPk' => $postPk, ':userPk' => $userPk]);
        $current = $checkStmt->fetchColumn();
        if ($current === false) {
            throw new Exception("You do not have permission to update this post.", 403);
        }
        if (trim((string)$current) === trim((string)$message)) {
            throw new Exception("Please change something to update your post", 400);
        }

        $sql = "UPDATE posts SET post_message = :postMessage, updated_at = NOW() WHERE post_pk = :postPk";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':postMessage', $message);
        $stmt->bindValue(':postPk', $postPk);
        return $stmt->execute();
    }

    public function deletePost(string $postPk, string $userPk): bool {
        $this->ensureDb();
        global $_db;
        $checkSql = "SELECT 1 FROM posts WHERE post_pk = :postPk AND post_user_fk = :userPk AND deleted_at IS NULL";
        $checkStmt = $_db->prepare($checkSql);
        $checkStmt->execute([':postPk' => $postPk, ':userPk' => $userPk]);
        if (!$checkStmt->fetchColumn()) {
            throw new Exception("You do not have permission to delete this post.", 403);
        }

        $sql = "UPDATE posts SET deleted_at = NOW() WHERE post_pk = :postPk";
        $stmt = $_db->prepare($sql);
        return $stmt->execute([':postPk' => $postPk]);
    }

    private function attachMeta($posts, $currentUserPk = null) {
        $this->ensureDb();
        global $_db;
        if (!$posts) return [];

        foreach ($posts as &$post) {
            $rootPostPk = $post['root_post_pk'] ?? $post['post_pk'] ?? null;
            if (empty($post['display_created_at'])) {
                $post['display_created_at'] = $post['created_at'] ?? null;
            }

            $commentTargetPk = $post['comment_target_pk'] ?? $post['post_pk'] ?? null;
            if (!$commentTargetPk) {
                $commentTargetPk = $post['post_pk'] ?? '';
            }
            $post['comment_target_pk'] = $commentTargetPk;
            $post['root_post_pk'] = $rootPostPk ?: $commentTargetPk;

            $commentCount = 0;
            $commentTargets = array_unique(array_filter([$commentTargetPk, $rootPostPk]));
            foreach ($commentTargets as $target) {
                // Get the original post PK if this is a repost
                $originalPostPk = $this->getOriginalPostPk($target);
                $stmt = $_db->prepare(
                    "SELECT COUNT(*) FROM comments WHERE comment_post_fk = :id AND deleted_at IS NULL"
                );
                $stmt->bindValue(":id", $originalPostPk);
                $stmt->execute();
                $commentCount += (int) $stmt->fetchColumn();
            }
            $post["comment_count"] = $commentCount;

            // Get the original post PK for likes
            $originalPostPkForLikes = $this->getOriginalPostPk($post["post_pk"]);
            $stmt = $_db->prepare(
                "SELECT COUNT(*) FROM likes l JOIN users u ON l.like_user_fk = u.user_pk WHERE l.like_post_fk = :id AND u.deleted_at IS NULL"
            );
            $stmt->bindValue(":id", $originalPostPkForLikes);
            $stmt->execute();
            $post["like_count"] = (int) $stmt->fetchColumn();

            if ($this->ensureRepostsAvailable()) {
                // Count reposts for the original post
                $originalPostPkForReposts = $this->getOriginalPostPk($post["post_pk"]);
                $stmt = $_db->prepare("SELECT COUNT(*) FROM reposts WHERE :id IN (repost_post_pk, repost_like_pk, repost_comment_pk)");
                $stmt->bindValue(":id", $originalPostPkForReposts);
                $stmt->execute();
                $post["repost_count"] = (int) $stmt->fetchColumn();
            } else {
                $post["repost_count"] = 0;
            }

            if ($currentUserPk) {
                // Get the original post PK for user-specific checks
                $originalPostPkForUserChecks = $this->getOriginalPostPk($post["post_pk"]);
                $stmt = $_db->prepare("
                    SELECT COUNT(*) 
                    FROM likes 
                    WHERE like_post_fk = :post AND like_user_fk = :user
                ");
                $stmt->bindValue(":post", $originalPostPkForUserChecks);
                $stmt->bindValue(":user", $currentUserPk);
                $stmt->execute();
                $post["is_liked_by_user"] = $stmt->fetchColumn() > 0;

                if ($this->ensureRepostsAvailable()) {
                    $stmt = $_db->prepare("
                        SELECT COUNT(*) 
                        FROM reposts 
                        WHERE :post IN (repost_post_pk, repost_like_pk, repost_comment_pk) AND repost_user_fk = :user
                    ");
                    $stmt->bindValue(":post", $originalPostPkForUserChecks);
                    $stmt->bindValue(":user", $currentUserPk);
                    $stmt->execute();
                    $post["is_reposted_by_user"] = $stmt->fetchColumn() > 0;
                } else {
                    $post["is_reposted_by_user"] = false;
                }
            } else {
                $post["is_liked_by_user"] = false;
                $post["is_reposted_by_user"] = false;
            }
        }

        return $posts;
    }
    
    private function getOriginalPostPk($targetPk) {
        $this->ensureDb();
        global $_db;
        
        // If this is a repost, get the original post
        $stmt = $_db->prepare("
            SELECT COALESCE(repost_post_pk, repost_like_pk, repost_comment_pk) as original_pk
            FROM reposts 
            WHERE repost_pk = :targetPk
        ");
        $stmt->bindValue(':targetPk', $targetPk);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['original_pk'])) {
            return $result['original_pk'];
        }
        
        // If not a repost, return the original target
        return $targetPk;
    }

    private function normalizeUser($currentUserPk = null) {
        return $currentUserPk ?: null;
    }

    private function ensureRepostsAvailable(): bool {
        $this->ensureDb();
        global $_db;
        if (!$_db) {
            $this->repostsTableExists = false;
            return false;
        }
        if ($this->repostsTableExists !== null) return $this->repostsTableExists;
        try {
            $stmt = $_db->query("SHOW TABLES LIKE 'reposts'");
            $this->repostsTableExists = (bool) ($stmt && $stmt->fetchColumn());
        } catch (Exception $e) {
            $this->repostsTableExists = false;
        }
        return $this->repostsTableExists;
    }
}
