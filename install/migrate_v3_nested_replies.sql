-- Migration v3: Nested replies (sub-comments) + reply likes
-- Run this on existing installations to add nested reply support

-- Add parent_reply_id for nested/threaded replies (like Facebook)
ALTER TABLE `{PREFIX}forum_replies` ADD COLUMN `parent_reply_id` INT DEFAULT NULL AFTER `post_id`;
ALTER TABLE `{PREFIX}forum_replies` ADD COLUMN `edited_at` DATETIME DEFAULT NULL AFTER `created_at`;

-- Add index for faster nested reply lookups
ALTER TABLE `{PREFIX}forum_replies` ADD INDEX `idx_parent_reply` (`parent_reply_id`);
ALTER TABLE `{PREFIX}forum_replies` ADD INDEX `idx_post_parent` (`post_id`, `parent_reply_id`);

-- Add index for reply likes
ALTER TABLE `{PREFIX}forum_likes` ADD INDEX `idx_reply_likes` (`reply_id`, `user_id`);
