# Implementation of Separate Repost Types (likes, comments, posts)

## Overview
This document describes the implementation of separate repost functionality for likes, comments, and posts. Previously, all types of reposts were linked to a post_pk, but now we support distinct reposting of each content type.

## Database Changes

### Table Structure Update
The `reposts` table needs to be updated to support the new structure:

#### Columns to Add:
- `repost_like_pk` (CHAR(50), NULLABLE) - References a like record
- `repost_comment_pk` (CHAR(50), NULLABLE) - References a comment record  
- `repost_post_pk` (CHAR(50), NULLABLE) - References a post record

#### Column to Remove:
- `repost_post_fk` - Old column that referenced posts only

#### Migration Script
Run the following SQL commands to update the table:

```sql
-- First, add the new columns
ALTER TABLE reposts 
ADD COLUMN repost_like_pk CHAR(50) NULL,
ADD COLUMN repost_comment_pk CHAR(50) NULL,
ADD COLUMN repost_post_pk CHAR(50) NULL;

-- Then, populate the new repost_post_pk column with values from the old repost_post_fk column
UPDATE reposts SET repost_post_pk = repost_post_fk WHERE repost_post_fk IS NOT NULL;

-- Finally, drop the old column
ALTER TABLE reposts 
DROP COLUMN repost_post_fk;
```

> **Note:** Make sure to backup your database before running these commands.

## Code Changes

### 1. API Endpoint: `api-repost.php`

Updated to handle different types of content:
- Accepts `post-pk`, `like-pk`, or `comment-pk` as GET parameters
- Validates that exactly one parameter is provided
- Checks if the target content exists before creating repost
- Creates notification with appropriate message based on content type
- Uses the correct field in the database according to content type

### 2. Model: `PostModel.php`

Updated SQL queries to work with the new column structure:

#### Feed Queries
- Modified all UNION queries to join with `COALESCE(repost_post_pk, repost_like_pk, repost_comment_pk)` instead of just `repost_post_fk`
- This allows fetching posts from any type of repost

#### Meta Data Queries  
- Updated `repost_count` calculation to check all three possible fields
- Updated `is_reposted_by_user` check to look across all three fields

## Validation Requirements

A validation method should be implemented to ensure that for each record in the `reposts` table, exactly one of the three PK fields (`repost_like_pk`, `repost_comment_pk`, `repost_post_pk`) is populated while the others remain NULL.

## Frontend Integration

To use the new functionality, frontend code should pass the appropriate parameter:
- For reposting posts: `?post-pk=<post_id>`
- For reposting likes: `?like-pk=<like_id>`  
- For reposting comments: `?comment-pk=<comment_id>`

## Testing

After implementing the database changes and code updates:
1. Test reposting posts still works
2. Test reposting likes works
3. Test reposting comments works
4. Verify feeds still display correctly
5. Verify notification system works properly
6. Confirm repost counts are calculated correctly