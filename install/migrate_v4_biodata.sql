-- Migration v4: Biodata siswa - tambah field tanggal lahir & kompetensi keahlian
-- Run this migration after schema.sql and previous migrations

ALTER TABLE `se_users` ADD COLUMN `birth_date` DATE DEFAULT NULL AFTER `bio`;
ALTER TABLE `se_users` ADD COLUMN `competency_id` INT DEFAULT NULL AFTER `birth_date`;

CREATE TABLE IF NOT EXISTS `se_competencies` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample competencies for SMK
INSERT INTO `se_competencies` (name, description) VALUES
('Teknik Komputer dan Jaringan', 'Kompetensi keahlian bidang jaringan komputer'),
('Rekayasa Perangkat Lunak', 'Kompetensi keahlian bidang pengembangan software'),
('Multimedia', 'Kompetensi keahlian bidang desain dan multimedia'),
('Akuntansi dan Keuangan Lembaga', 'Kompetensi keahlian bidang akuntansi'),
('Bisnis Daring dan Pemasaran', 'Kompetensi keahlian bidang pemasaran digital');
