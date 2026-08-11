-- =====================================================================
-- SIABDI - Sistem Absensi Sekolah Berbasis QR Code
-- SQL Import: struktur tabel + data seeding (master data)
--
-- Gunakan file ini sebagai alternatif dari `php spark migrate --all`
-- untuk shared hosting yang tidak menyediakan akses SSH/terminal.
-- Import file ini melalui phpMyAdmin / Adminer pada database kosong.
--
-- Setelah import selesai, tabel `migrations` diisi manual agar
-- CodeIgniter menganggap semua migrasi sudah pernah dijalankan
-- (mencegah error saat spark/migrasi otomatis dijalankan di kemudian hari).
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Struktur Tabel
-- ---------------------------------------------------------------------

-- Tabel otentikasi (Myth\Auth)
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `password_hash` varchar(255) NOT NULL,
  `reset_hash` varchar(255) DEFAULT NULL,
  `reset_at` datetime DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `activate_hash` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_message` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `force_pass_reset` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_groups_permissions` (
  `group_id` int unsigned NOT NULL DEFAULT '0',
  `permission_id` int unsigned NOT NULL DEFAULT '0',
  KEY `auth_groups_permissions_permission_id_foreign` (`permission_id`),
  KEY `group_id_permission_id` (`group_id`,`permission_id`),
  CONSTRAINT `auth_groups_permissions_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `auth_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auth_groups_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `auth_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_groups_users` (
  `group_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  KEY `auth_groups_users_user_id_foreign` (`user_id`),
  KEY `group_id_user_id` (`group_id`,`user_id`),
  CONSTRAINT `auth_groups_users_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `auth_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_users_permissions` (
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `permission_id` int unsigned NOT NULL DEFAULT '0',
  KEY `auth_users_permissions_permission_id_foreign` (`permission_id`),
  KEY `user_id_permission_id` (`user_id`,`permission_id`),
  CONSTRAINT `auth_users_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `auth_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auth_users_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_logins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `selector` varchar(255) NOT NULL,
  `hashedValidator` varchar(255) NOT NULL,
  `user_id` int unsigned NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_tokens_user_id_foreign` (`user_id`),
  KEY `selector` (`selector`),
  CONSTRAINT `auth_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_activation_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `auth_reset_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Tabel referensi/master
CREATE TABLE `tb_jurusan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `jurusan` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jurusan` (`jurusan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `tb_kelas` (
  `id_kelas` int unsigned NOT NULL AUTO_INCREMENT,
  `kelas` varchar(32) NOT NULL,
  `id_jurusan` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kelas`),
  KEY `tb_kelas_id_jurusan_foreign` (`id_jurusan`),
  CONSTRAINT `tb_kelas_id_jurusan_foreign` FOREIGN KEY (`id_jurusan`) REFERENCES `tb_jurusan` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `tb_kehadiran` (
  `id_kehadiran` int NOT NULL AUTO_INCREMENT,
  `kehadiran` enum('Hadir','Sakit','Izin','Tanpa keterangan') NOT NULL,
  PRIMARY KEY (`id_kehadiran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabel data guru & siswa
CREATE TABLE `tb_guru` (
  `id_guru` int NOT NULL AUTO_INCREMENT,
  `nuptk` varchar(24) NOT NULL,
  `nama_guru` varchar(255) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(32) NOT NULL,
  `unique_code` varchar(64) NOT NULL,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `unique_code` (`unique_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_siswa` (
  `id_siswa` int NOT NULL AUTO_INCREMENT,
  `nis` varchar(16) NOT NULL,
  `nama_siswa` varchar(255) NOT NULL,
  `id_kelas` int unsigned NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `no_hp` varchar(32) NOT NULL,
  `unique_code` varchar(64) NOT NULL,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `unique_code` (`unique_code`),
  KEY `id_kelas` (`id_kelas`),
  CONSTRAINT `tb_siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabel presensi
CREATE TABLE `tb_presensi_guru` (
  `id_presensi` int NOT NULL AUTO_INCREMENT,
  `id_guru` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `id_kehadiran` int NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  PRIMARY KEY (`id_presensi`),
  KEY `id_kehadiran` (`id_kehadiran`),
  KEY `id_guru` (`id_guru`),
  CONSTRAINT `tb_presensi_guru_ibfk_2` FOREIGN KEY (`id_kehadiran`) REFERENCES `tb_kehadiran` (`id_kehadiran`),
  CONSTRAINT `tb_presensi_guru_ibfk_3` FOREIGN KEY (`id_guru`) REFERENCES `tb_guru` (`id_guru`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_presensi_siswa` (
  `id_presensi` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `id_kelas` int unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_dzuhur` time DEFAULT NULL,
  `jam_ashar` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `id_kehadiran` int NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  PRIMARY KEY (`id_presensi`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_kehadiran` (`id_kehadiran`),
  KEY `id_kelas` (`id_kelas`),
  CONSTRAINT `tb_presensi_siswa_ibfk_2` FOREIGN KEY (`id_kehadiran`) REFERENCES `tb_kehadiran` (`id_kehadiran`),
  CONSTRAINT `tb_presensi_siswa_ibfk_3` FOREIGN KEY (`id_siswa`) REFERENCES `tb_siswa` (`id_siswa`) ON DELETE CASCADE,
  CONSTRAINT `tb_presensi_siswa_ibfk_4` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id_kelas`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabel pengaturan umum
CREATE TABLE `general_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(225) DEFAULT NULL,
  `school_name` varchar(225) DEFAULT 'SMK 1 Indonesia',
  `school_year` varchar(225) DEFAULT '2024/2025',
  `batas_absen_masuk` time NOT NULL DEFAULT '07:20:00',
  `copyright` varchar(225) DEFAULT '© 2025 All rights reserved.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Tabel log migrasi CodeIgniter (agar `php spark migrate` tidak mengulang migrasi di bawah ini)
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ---------------------------------------------------------------------
-- Data Seeding (master data)
-- ---------------------------------------------------------------------

-- Status kehadiran
INSERT INTO `tb_kehadiran` (`id_kehadiran`, `kehadiran`) VALUES
(1, 'Hadir'),
(2, 'Sakit'),
(3, 'Izin'),
(4, 'Tanpa keterangan');

-- Jurusan
INSERT INTO `tb_jurusan` (`id`, `jurusan`) VALUES
(1, 'OTKP'),
(2, 'BDP'),
(3, 'AKL'),
(4, 'RPL');

-- Kelas (X, XI, XII x 4 jurusan)
INSERT INTO `tb_kelas` (`id_kelas`, `kelas`, `id_jurusan`) VALUES
(1, 'X', 1),
(2, 'X', 2),
(3, 'X', 3),
(4, 'X', 4),
(5, 'XI', 1),
(6, 'XI', 2),
(7, 'XI', 3),
(8, 'XI', 4),
(9, 'XII', 1),
(10, 'XII', 2),
(11, 'XII', 3),
(12, 'XII', 4);

-- Pengaturan umum default
INSERT INTO `general_settings` (`id`, `school_name`, `school_year`, `batas_absen_masuk`, `copyright`) VALUES
(1, 'SMK 1 Indonesia', '2024/2025', '07:20:00', '© 2025 All rights reserved.');

-- Akun superadmin default
-- username : superadmin
-- password : superadmin  (ganti setelah login pertama kali!)
INSERT INTO `users` (`id`, `email`, `username`, `is_superadmin`, `password_hash`, `active`) VALUES
(1, 'adminsuper@gmail.com', 'superadmin', 1, '$2y$10$GvSqDAyxAiiDYZIkO.L1Ue9nagDqj2YYejQUb/fH.W3jLllL9y5yC', 1);

-- Tandai seluruh migrasi bawaan sebagai sudah dijalankan
INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
('2017-11-20-223112', '\\Myth\\Auth\\Database\\Migrations\\CreateAuthTables', 'default', 'Myth\\Auth', UNIX_TIMESTAMP(), 1),
('2023-08-18-000001', '\\App\\Database\\Migrations\\CreateJurusanTable', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2023-08-18-000002', '\\App\\Database\\Migrations\\CreateKelasTable', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2023-08-18-000003', '\\App\\Database\\Migrations\\CreateDB', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2023-08-18-000004', '\\App\\Database\\Migrations\\AddSuperadmin', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2024-07-24-083011', '\\App\\Database\\Migrations\\GeneralSettings', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2026-08-11-133000', '\\App\\Database\\Migrations\\AddShalatColumns', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2026-08-11-140000', '\\App\\Database\\Migrations\\AddBatasAbsenMasuk', 'default', 'App', UNIX_TIMESTAMP(), 1);

SET FOREIGN_KEY_CHECKS = 1;
