-- Migration V5: Add missing tables and columns
-- Run this if you get errors about missing tables/columns

-- Competencies table (for SMK)
CREATE TABLE IF NOT EXISTS `{PREFIX}competencies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `code` VARCHAR(50) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to users table (safe - uses IF NOT EXISTS pattern via ALTER IGNORE)
-- Note: Run these one by one if your MySQL version doesn't support IF NOT EXISTS in ALTER
ALTER TABLE `{PREFIX}users` ADD COLUMN IF NOT EXISTS `class_id` INT DEFAULT NULL;
ALTER TABLE `{PREFIX}users` ADD COLUMN IF NOT EXISTS `competency_id` INT DEFAULT NULL;
ALTER TABLE `{PREFIX}users` ADD COLUMN IF NOT EXISTS `birth_date` DATE DEFAULT NULL;
ALTER TABLE `{PREFIX}users` ADD COLUMN IF NOT EXISTS `theme` VARCHAR(10) DEFAULT 'light';

-- Add subject_id to forum_posts (for subject-specific discussions)
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN IF NOT EXISTS `subject_id` INT DEFAULT NULL;
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN IF NOT EXISTS `edited_at` DATETIME DEFAULT NULL;
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN IF NOT EXISTS `edited_by` INT DEFAULT NULL;

-- Add parent_reply_id and edited_at to forum_replies (for nested replies)
ALTER TABLE `{PREFIX}forum_replies` ADD COLUMN IF NOT EXISTS `parent_reply_id` INT DEFAULT NULL;
ALTER TABLE `{PREFIX}forum_replies` ADD COLUMN IF NOT EXISTS `edited_at` DATETIME DEFAULT NULL;


-- Teacher-Subject assignments (guru bisa mengajar banyak mapel)
CREATE TABLE IF NOT EXISTS `{PREFIX}teacher_subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `{PREFIX}users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `{PREFIX}subjects`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_teacher_subject` (`teacher_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Add status column to assignments (open/closed)
ALTER TABLE `{PREFIX}assignments` ADD COLUMN IF NOT EXISTS `status` ENUM('open','closed') DEFAULT 'open';
