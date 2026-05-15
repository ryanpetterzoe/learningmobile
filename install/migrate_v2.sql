-- SimpleEdu Migration v2
-- Jalankan query ini di phpMyAdmin untuk update database yang sudah ada

-- 1. Tambah kolom class_id di tabel users (untuk siswa pilih kelas)
ALTER TABLE `{PREFIX}users` ADD COLUMN `class_id` INT DEFAULT NULL AFTER `nip`;

-- 2. Tambah kolom class_id di tabel forum_posts (untuk filter diskusi per kelas)
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN `class_id` INT DEFAULT NULL AFTER `category_id`;

-- 3. Tambah kolom subject_id di forum_posts (untuk tag mapel di diskusi)
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN `subject_id` INT DEFAULT NULL AFTER `class_id`;

-- 4. Tambah kolom edited_at dan edited_by di forum_posts
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN `edited_at` DATETIME DEFAULT NULL;
ALTER TABLE `{PREFIX}forum_posts` ADD COLUMN `edited_by` INT DEFAULT NULL;

-- 5. Tambah kolom edited_at dan edited_by di forum_replies
ALTER TABLE `{PREFIX}forum_replies` ADD COLUMN `edited_at` DATETIME DEFAULT NULL;
ALTER TABLE `{PREFIX}forum_replies` ADD COLUMN `edited_by` INT DEFAULT NULL;

-- 6. Buat tabel teacher_subjects (guru bisa mengajar banyak mapel)
CREATE TABLE IF NOT EXISTS `{PREFIX}teacher_subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `{PREFIX}users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `{PREFIX}subjects`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_teacher_subject` (`teacher_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tambah kolom attachment di quiz_questions (untuk foto/video di soal)
ALTER TABLE `{PREFIX}quiz_questions` ADD COLUMN `attachment` VARCHAR(500) DEFAULT NULL AFTER `question`;
