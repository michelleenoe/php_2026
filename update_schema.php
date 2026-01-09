<?php
// Script to update database schema for reposts as independent entities
require_once __DIR__ . '/db.php';

global $_db;

echo "Updating database schema...\n";

try {
    // Add like_target_type column to likes table
    echo "Adding like_target_type column to likes table...\n";
    try {
        $_db->exec("ALTER TABLE likes ADD COLUMN like_target_type ENUM('post', 'repost') DEFAULT 'post' AFTER like_post_fk;");
        echo "✓ Added like_target_type column to likes table\n";
    } catch (Exception $e) {
        // Column might already exist
        echo "! Column like_target_type might already exist: " . $e->getMessage() . "\n";
    }

    // Update the foreign key constraint to allow NULL for like_post_fk (we'll use like_repost_fk for reposts)
    echo "Modifying likes table structure...\n";
    try {
        // First drop the existing foreign key
        $_db->exec("ALTER TABLE likes DROP FOREIGN KEY likes_ibfk_1;");
        echo "✓ Dropped existing foreign key constraint\n";
    } catch (Exception $e) {
        echo "! Foreign key might not exist: " . $e->getMessage() . "\n";
    }
    
    // Change like_post_fk to allow NULL
    $_db->exec("ALTER TABLE likes MODIFY COLUMN like_post_fk CHAR(50) NULL;");
    echo "✓ Modified like_post_fk to allow NULL\n";
    
    // Add like_repost_fk column
    try {
        $_db->exec("ALTER TABLE likes ADD COLUMN like_repost_fk CHAR(50) NULL AFTER like_post_fk;");
        echo "✓ Added like_repost_fk column\n";
    } catch (Exception $e) {
        echo "! Column like_repost_fk might already exist: " . $e->getMessage() . "\n";
    }

    // Add foreign key for like_repost_fk
    try {
        $_db->exec("ALTER TABLE likes ADD CONSTRAINT fk_likes_repost FOREIGN KEY (like_repost_fk) REFERENCES reposts(repost_pk) ON DELETE CASCADE;");
        echo "✓ Added foreign key constraint for like_repost_fk\n";
    } catch (Exception $e) {
        echo "! Foreign key constraint for like_repost_fk might already exist: " . $e->getMessage() . "\n";
    }

    // Now do the same for comments table
    echo "Updating comments table structure...\n";
    try {
        // Add comment_target_type column
        $_db->exec("ALTER TABLE comments ADD COLUMN comment_target_type ENUM('post', 'repost') DEFAULT 'post' AFTER comment_post_fk;");
        echo "✓ Added comment_target_type column to comments table\n";
    } catch (Exception $e) {
        echo "! Column comment_target_type might already exist: " . $e->getMessage() . "\n";
    }

    // Modify comment_post_fk to allow NULL
    $_db->exec("ALTER TABLE comments MODIFY COLUMN comment_post_fk VARCHAR(50) NULL;");
    echo "✓ Modified comment_post_fk to allow NULL\n";
    
    // Add comment_repost_fk column
    try {
        $_db->exec("ALTER TABLE comments ADD COLUMN comment_repost_fk CHAR(50) NULL AFTER comment_post_fk;");
        echo "✓ Added comment_repost_fk column\n";
    } catch (Exception $e) {
        echo "! Column comment_repost_fk might already exist: " . $e->getMessage() . "\n";
    }

    // Add foreign key for comment_repost_fk
    try {
        $_db->exec("ALTER TABLE comments ADD CONSTRAINT fk_comments_repost FOREIGN KEY (comment_repost_fk) REFERENCES reposts(repost_pk) ON DELETE CASCADE;");
        echo "✓ Added foreign key constraint for comment_repost_fk\n";
    } catch (Exception $e) {
        echo "! Foreign key constraint for comment_repost_fk might already exist: " . $e->getMessage() . "\n";
    }

    // Recreate the primary foreign key constraint for likes
    $_db->exec("ALTER TABLE likes ADD CONSTRAINT likes_ibfk_1 FOREIGN KEY (like_post_fk) REFERENCES posts(post_pk) ON DELETE CASCADE;");
    echo "✓ Re-added foreign key constraint for like_post_fk\n";

    echo "\nDatabase schema update completed!\n";
} catch (Exception $e) {
    echo "Error updating database schema: " . $e->getMessage() . "\n";
}