<?php
// Database update script for reposts table
require_once __DIR__ . '/db.php';

global $_db;

try {
    // First, check if the old column exists before dropping it
    $checkCol = $_db->query("SHOW COLUMNS FROM reposts LIKE 'repost_post_fk'");
    if ($checkCol->rowCount() > 0) {
        echo "Dropping old column repost_post_fk...\n";
        $_db->exec("ALTER TABLE reposts DROP COLUMN repost_post_fk");
        echo "Old column dropped successfully.\n";
    } else {
        echo "Column repost_post_fk does not exist, continuing...\n";
    }

    // Check if new columns exist before adding them
    $columnsToAdd = [];
    
    $checkLike = $_db->query("SHOW COLUMNS FROM reposts LIKE 'repost_like_pk'");
    if ($checkLike->rowCount() == 0) {
        $columnsToAdd[] = "ADD COLUMN repost_like_pk CHAR(50) NULL";
    }
    
    $checkComment = $_db->query("SHOW COLUMNS FROM reposts LIKE 'repost_comment_pk'");
    if ($checkComment->rowCount() == 0) {
        $columnsToAdd[] = "ADD COLUMN repost_comment_pk CHAR(50) NULL";
    }
    
    $checkPost = $_db->query("SHOW COLUMNS FROM reposts LIKE 'repost_post_pk'");
    if ($checkPost->rowCount() == 0) {
        $columnsToAdd[] = "ADD COLUMN repost_post_pk CHAR(50) NULL";
    }
    
    if (!empty($columnsToAdd)) {
        $addColumnQuery = "ALTER TABLE reposts " . implode(", ", $columnsToAdd);
        echo "Adding new columns...\n";
        $_db->exec($addColumnQuery);
        echo "New columns added successfully.\n";
    } else {
        echo "All new columns already exist, skipping...\n";
    }

    echo "Database update completed successfully!\n";
} catch (Exception $e) {
    echo "Error during database update: " . $e->getMessage() . "\n";
}