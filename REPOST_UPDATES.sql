-- Database schema updates for reposts as independent entities

-- Add columns to likes table to support targeting both posts and reposts
ALTER TABLE likes ADD COLUMN like_target_type ENUM('post', 'repost') DEFAULT 'post' AFTER like_post_fk;
ALTER TABLE likes MODIFY COLUMN like_post_fk CHAR(50) NULL;
ALTER TABLE likes ADD COLUMN like_repost_fk CHAR(50) NULL AFTER like_post_fk;

-- Add foreign key constraint for like_repost_fk
ALTER TABLE likes ADD CONSTRAINT fk_likes_repost FOREIGN KEY (like_repost_fk) REFERENCES reposts(repost_pk) ON DELETE CASCADE;

-- Recreate the primary foreign key constraint for likes
ALTER TABLE likes DROP FOREIGN KEY likes_ibfk_1;
ALTER TABLE likes ADD CONSTRAINT likes_ibfk_1 FOREIGN KEY (like_post_fk) REFERENCES posts(post_pk) ON DELETE CASCADE;

-- Add columns to comments table to support targeting both posts and reposts
ALTER TABLE comments ADD COLUMN comment_target_type ENUM('post', 'repost') DEFAULT 'post' AFTER comment_post_fk;
ALTER TABLE comments MODIFY COLUMN comment_post_fk VARCHAR(50) NULL;
ALTER TABLE comments ADD COLUMN comment_repost_fk CHAR(50) NULL AFTER comment_post_fk;

-- Add foreign key constraint for comment_repost_fk
ALTER TABLE comments ADD CONSTRAINT fk_comments_repost FOREIGN KEY (comment_repost_fk) REFERENCES reposts(repost_pk) ON DELETE CASCADE;

-- Recreate the primary foreign key constraint for comments
ALTER TABLE comments DROP FOREIGN KEY comments_ibfk_1;
ALTER TABLE comments ADD CONSTRAINT comments_ibfk_1 FOREIGN KEY (comment_post_fk) REFERENCES posts(post_pk) ON DELETE CASCADE;