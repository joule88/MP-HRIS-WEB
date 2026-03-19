-- Manual SQL script untuk create face_registrations table
-- Run this manually via phpMyAdmin or MySQL Workbench jika migration gagal

CREATE TABLE IF NOT EXISTS `face_registrations` (
  `id_face_registration` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_pegawai` BIGINT UNSIGNED NOT NULL,
  `face_embedding` TEXT NULL COMMENT 'JSON array dari Python face recognition service',
  `foto_pendaftaran` VARCHAR(255) NULL,
  `status_verifikasi` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `verified_by` BIGINT UNSIGNED NULL,
  `verified_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  -- Foreign Keys (hapus jika error, lalu run manual FK setelah pastikan kolom exist)
  CONSTRAINT `fk_face_reg_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE CASCADE,
  CONSTRAINT `fk_face_reg_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
