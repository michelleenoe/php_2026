-- Update the reposts table to support reposting of likes, comments, and posts separately
-- First, add the new columns
ALTER TABLE reposts 
ADD COLUMN repost_like_pk CHAR(50) NULL,
ADD COLUMN repost_comment_pk CHAR(50) NULL,
ADD COLUMN repost_post_pk CHAR(50) NULL;

-- Then, populate the new repost_post_pk column with values from the old repost_post_fk column
UPDATE reposts SET repost_post_pk = repost_post_fk;

-- Finally, drop the old column
ALTER TABLE reposts 
DROP COLUMN repost_post_fk;

-- Add foreign key constraints if needed later after verifying the changes work correctly