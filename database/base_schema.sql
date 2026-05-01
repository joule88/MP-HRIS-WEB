-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_mpg
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `approval_surat`
--

DROP TABLE IF EXISTS `approval_surat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_surat` (
  `id_approval` varchar(20) NOT NULL,
  `id_surat` varchar(20) NOT NULL,
  `id_approver` bigint(20) unsigned NOT NULL,
  `id_ttd_approver` varchar(20) DEFAULT NULL,
  `tahap` tinyint(4) NOT NULL COMMENT '1=Manajer, 2=HRD',
  `status` enum('disetujui','ditolak') NOT NULL DEFAULT 'disetujui',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_approval`),
  UNIQUE KEY `approval_surat_id_surat_tahap_unique` (`id_surat`,`tahap`),
  KEY `approval_surat_id_approver_foreign` (`id_approver`),
  KEY `approval_surat_id_ttd_approver_foreign` (`id_ttd_approver`),
  CONSTRAINT `approval_surat_id_approver_foreign` FOREIGN KEY (`id_approver`) REFERENCES `users` (`id`),
  CONSTRAINT `approval_surat_id_surat_foreign` FOREIGN KEY (`id_surat`) REFERENCES `surat_izin` (`id_surat`) ON DELETE CASCADE,
  CONSTRAINT `approval_surat_id_ttd_approver_foreign` FOREIGN KEY (`id_ttd_approver`) REFERENCES `tanda_tangan` (`id_tanda_tangan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_wajah`
--

DROP TABLE IF EXISTS `data_wajah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_wajah` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned NOT NULL,
  `path_model_yml` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0:Pending, 1:Approved, 2:Rejected',
  `last_updated` timestamp NULL DEFAULT NULL,
  `encoding_wajah` text DEFAULT NULL,
  `tanggal_latih` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_wajah_id_user_foreign` (`id_user`),
  CONSTRAINT `data_wajah_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detail_penggunaan_poin`
--

DROP TABLE IF EXISTS `detail_penggunaan_poin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_penggunaan_poin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_penggunaan` varchar(20) NOT NULL,
  `id_poin_sumber` bigint(20) unsigned NOT NULL,
  `jumlah_diambil` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_penggunaan_poin_id_poin_sumber_foreign` (`id_poin_sumber`),
  KEY `detail_penggunaan_poin_id_penggunaan_foreign` (`id_penggunaan`),
  CONSTRAINT `detail_penggunaan_poin_id_penggunaan_foreign` FOREIGN KEY (`id_penggunaan`) REFERENCES `penggunaan_poin` (`id_penggunaan`) ON DELETE CASCADE,
  CONSTRAINT `detail_penggunaan_poin_id_poin_sumber_foreign` FOREIGN KEY (`id_poin_sumber`) REFERENCES `poin_lembur` (`id_poin`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detail_role_permissions`
--

DROP TABLE IF EXISTS `detail_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_role_permissions` (
  `id_role` bigint(20) unsigned NOT NULL,
  `id_permission` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_role`,`id_permission`),
  KEY `detail_role_permissions_id_permission_foreign` (`id_permission`),
  CONSTRAINT `detail_role_permissions_id_permission_foreign` FOREIGN KEY (`id_permission`) REFERENCES `permissions` (`id_permission`) ON DELETE CASCADE,
  CONSTRAINT `detail_role_permissions_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detail_user_roles`
--

DROP TABLE IF EXISTS `detail_user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_user_roles` (
  `id_user` bigint(20) unsigned NOT NULL,
  `id_role` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_user`,`id_role`),
  KEY `detail_user_roles_id_role_foreign` (`id_role`),
  CONSTRAINT `detail_user_roles_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE CASCADE,
  CONSTRAINT `detail_user_roles_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned NOT NULL,
  `fcm_token` varchar(255) NOT NULL,
  `device_type` varchar(20) NOT NULL DEFAULT 'android',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_fcm_token_unique` (`fcm_token`),
  KEY `device_tokens_id_user_index` (`id_user`),
  CONSTRAINT `device_tokens_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `divisi`
--

DROP TABLE IF EXISTS `divisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `divisi` (
  `id_divisi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_divisi` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_divisi`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;




--
-- Table structure for table `hari_libur`
--

DROP TABLE IF EXISTS `hari_libur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hari_libur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `id_kantor` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hari_libur_id_kantor_foreign` (`id_kantor`),
  CONSTRAINT `hari_libur_id_kantor_foreign` FOREIGN KEY (`id_kantor`) REFERENCES `kantor` (`id_kantor`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jabatan`
--

DROP TABLE IF EXISTS `jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jabatan` (
  `id_jabatan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jadwal_kerja`
--

DROP TABLE IF EXISTS `jadwal_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jadwal_kerja` (
  `id_jadwal` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `id_shift` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `jadwal_kerja_id_shift_foreign` (`id_shift`),
  KEY `idx_jadwal_tanggal` (`tanggal`),
  KEY `jadwal_kerja_id_user_tanggal_index` (`id_user`,`tanggal`),
  CONSTRAINT `jadwal_kerja_id_shift_foreign` FOREIGN KEY (`id_shift`) REFERENCES `shift_kerja` (`id_shift`),
  CONSTRAINT `jadwal_kerja_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4046 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jenis_izin`
--

DROP TABLE IF EXISTS `jenis_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_izin` (
  `id_jenis_izin` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_izin` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jenis_izin`),
  UNIQUE KEY `jenis_izin_nama_izin_unique` (`nama_izin`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jenis_kompensasi`
--

DROP TABLE IF EXISTS `jenis_kompensasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_kompensasi` (
  `id_kompensasi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kompensasi` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kompensasi`),
  UNIQUE KEY `jenis_kompensasi_nama_kompensasi_unique` (`nama_kompensasi`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jenis_pengurangan`
--

DROP TABLE IF EXISTS `jenis_pengurangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_pengurangan` (
  `id_pengurangan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_pengurangan` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pengurangan`),
  UNIQUE KEY `jenis_pengurangan_nama_pengurangan_unique` (`nama_pengurangan`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kantor`
--

DROP TABLE IF EXISTS `kantor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kantor` (
  `id_kantor` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kantor` varchar(50) NOT NULL,
  `alamat` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `radius` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kantor`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lembur`
--

DROP TABLE IF EXISTS `lembur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lembur` (
  `id_lembur` varchar(20) NOT NULL,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `tanggal_lembur` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `durasi_menit` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `jumlah_poin` int(11) DEFAULT NULL,
  `id_kompensasi` bigint(20) unsigned DEFAULT NULL,
  `id_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `alasan_penolakan` varchar(150) DEFAULT NULL,
  `tanggal_diajukan` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_lembur`),
  KEY `lembur_id_kompensasi_foreign` (`id_kompensasi`),
  KEY `lembur_id_status_index` (`id_status`),
  KEY `lembur_id_user_index` (`id_user`),
  CONSTRAINT `lembur_id_kompensasi_foreign` FOREIGN KEY (`id_kompensasi`) REFERENCES `jenis_kompensasi` (`id_kompensasi`),
  CONSTRAINT `lembur_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pengajuan` (`id_status`),
  CONSTRAINT `lembur_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifikasi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` varchar(50) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifikasi_id_user_is_read_index` (`id_user`,`is_read`),
  KEY `notifikasi_id_user_created_at_index` (`id_user`,`created_at`),
  CONSTRAINT `notifikasi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengajuan_izin`
--

DROP TABLE IF EXISTS `pengajuan_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengajuan_izin` (
  `id_izin` varchar(20) NOT NULL,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `id_jenis_izin` bigint(20) unsigned DEFAULT NULL,
  `alasan` varchar(150) DEFAULT NULL,
  `bukti_file` varchar(150) DEFAULT NULL,
  `id_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `alasan_penolakan` varchar(150) DEFAULT NULL,
  `tanggal_diajukan` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_izin`),
  KEY `pengajuan_izin_id_jenis_izin_foreign` (`id_jenis_izin`),
  KEY `pengajuan_izin_id_status_index` (`id_status`),
  KEY `pengajuan_izin_id_user_index` (`id_user`),
  CONSTRAINT `pengajuan_izin_id_jenis_izin_foreign` FOREIGN KEY (`id_jenis_izin`) REFERENCES `jenis_izin` (`id_jenis_izin`),
  CONSTRAINT `pengajuan_izin_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pengajuan` (`id_status`),
  CONSTRAINT `pengajuan_izin_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `penggunaan_poin`
--

DROP TABLE IF EXISTS `penggunaan_poin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penggunaan_poin` (
  `id_penggunaan` varchar(20) NOT NULL,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `tanggal_penggunaan` date NOT NULL,
  `jumlah_poin` int(11) DEFAULT NULL,
  `jam_masuk_custom` time DEFAULT NULL,
  `jam_pulang_custom` time DEFAULT NULL,
  `id_pengurangan` bigint(20) unsigned DEFAULT NULL,
  `id_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `alasan_penolakan` varchar(150) DEFAULT NULL,
  `tanggal_diajukan` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_penggunaan`),
  KEY `penggunaan_poin_id_pengurangan_foreign` (`id_pengurangan`),
  KEY `penggunaan_poin_id_user_tanggal_penggunaan_index` (`id_user`,`tanggal_penggunaan`),
  KEY `penggunaan_poin_id_status_index` (`id_status`),
  CONSTRAINT `penggunaan_poin_id_pengurangan_foreign` FOREIGN KEY (`id_pengurangan`) REFERENCES `jenis_pengurangan` (`id_pengurangan`),
  CONSTRAINT `penggunaan_poin_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pengajuan` (`id_status`),
  CONSTRAINT `penggunaan_poin_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengumuman`
--

DROP TABLE IF EXISTS `pengumuman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengumuman` (
  `id_pengumuman` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(80) DEFAULT NULL,
  `isi` varchar(500) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `dibuat_oleh` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pengumuman`),
  KEY `pengumuman_dibuat_oleh_foreign` (`dibuat_oleh`),
  CONSTRAINT `pengumuman_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id_permission` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_permission` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_permission`),
  UNIQUE KEY `permissions_nama_permission_unique` (`nama_permission`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poin_lembur`
--

DROP TABLE IF EXISTS `poin_lembur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poin_lembur` (
  `id_poin` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `jumlah_poin` int(11) DEFAULT NULL,
  `sisa_poin` int(11) DEFAULT NULL,
  `id_lembur` varchar(20) DEFAULT NULL,
  `keterangan` varchar(150) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `expired_at` date DEFAULT NULL,
  `is_fully_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_poin`),
  KEY `poin_lembur_id_user_index` (`id_user`),
  KEY `poin_lembur_tanggal_index` (`tanggal`),
  KEY `poin_lembur_id_lembur_foreign` (`id_lembur`),
  CONSTRAINT `poin_lembur_id_lembur_foreign` FOREIGN KEY (`id_lembur`) REFERENCES `lembur` (`id_lembur`) ON DELETE SET NULL,
  CONSTRAINT `poin_lembur_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `presensi`
--

DROP TABLE IF EXISTS `presensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presensi` (
  `id_presensi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `lat_masuk` decimal(10,8) DEFAULT NULL,
  `lon_masuk` decimal(11,8) DEFAULT NULL,
  `lat_pulang` decimal(10,8) DEFAULT NULL,
  `lon_pulang` decimal(11,8) DEFAULT NULL,
  `foto_wajah_masuk` varchar(255) DEFAULT NULL,
  `foto_wajah_pulang` varchar(255) DEFAULT NULL,
  `id_status` bigint(20) unsigned DEFAULT NULL,
  `alasan_telat` varchar(150) DEFAULT NULL,
  `keterangan_pulang` varchar(150) DEFAULT NULL,
  `waktu_terlambat` time DEFAULT NULL,
  `waktu_masuk_awal` time DEFAULT NULL,
  `waktu_pulang_awal` time DEFAULT NULL,
  `waktu_pulang_akhir` time DEFAULT NULL,
  `verifikasi_wajah` tinyint(1) NOT NULL DEFAULT 0,
  `id_validasi` bigint(20) unsigned NOT NULL DEFAULT 1,
  `keterangan_luar_radius` text DEFAULT NULL,
  `alasan_penolakan` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_presensi`),
  KEY `idx_presensi_tanggal` (`tanggal`),
  KEY `presensi_tanggal_index` (`tanggal`),
  KEY `presensi_id_user_index` (`id_user`),
  KEY `presensi_id_status_index` (`id_status`),
  KEY `presensi_id_validasi_index` (`id_validasi`),
  KEY `presensi_id_user_tanggal_index` (`id_user`,`tanggal`),
  CONSTRAINT `presensi_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_presensi` (`id_status`),
  CONSTRAINT `presensi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  CONSTRAINT `presensi_id_validasi_foreign` FOREIGN KEY (`id_validasi`) REFERENCES `status_validasi_presensi` (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `riwayat_tukar_shift`
--

DROP TABLE IF EXISTS `riwayat_tukar_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_tukar_shift` (
  `id_riwayat` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_1` bigint(20) unsigned NOT NULL,
  `id_jadwal_1` bigint(20) unsigned NOT NULL,
  `id_user_2` bigint(20) unsigned NOT NULL,
  `id_jadwal_2` bigint(20) unsigned NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_riwayat`),
  KEY `riwayat_tukar_shift_id_user_1_foreign` (`id_user_1`),
  KEY `riwayat_tukar_shift_id_jadwal_1_foreign` (`id_jadwal_1`),
  KEY `riwayat_tukar_shift_id_user_2_foreign` (`id_user_2`),
  KEY `riwayat_tukar_shift_id_jadwal_2_foreign` (`id_jadwal_2`),
  KEY `riwayat_tukar_shift_created_by_foreign` (`created_by`),
  CONSTRAINT `riwayat_tukar_shift_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_tukar_shift_id_jadwal_1_foreign` FOREIGN KEY (`id_jadwal_1`) REFERENCES `jadwal_kerja` (`id_jadwal`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_tukar_shift_id_jadwal_2_foreign` FOREIGN KEY (`id_jadwal_2`) REFERENCES `jadwal_kerja` (`id_jadwal`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_tukar_shift_id_user_1_foreign` FOREIGN KEY (`id_user_1`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_tukar_shift_id_user_2_foreign` FOREIGN KEY (`id_user_2`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_role` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `roles_nama_role_unique` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shift_kerja`
--

DROP TABLE IF EXISTS `shift_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_kerja` (
  `id_shift` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_shift` varchar(50) DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_shift`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status_pengajuan`
--

DROP TABLE IF EXISTS `status_pengajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pengajuan` (
  `id_status` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_status` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_status`),
  UNIQUE KEY `status_pengajuan_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status_presensi`
--

DROP TABLE IF EXISTS `status_presensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_presensi` (
  `id_status` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_status` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_status`),
  UNIQUE KEY `status_presensi_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status_validasi_presensi`
--

DROP TABLE IF EXISTS `status_validasi_presensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_validasi_presensi` (
  `id_status` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_status` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_status`),
  UNIQUE KEY `status_validasi_presensi_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_izin`
--

DROP TABLE IF EXISTS `surat_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surat_izin` (
  `id_surat` varchar(20) NOT NULL,
  `id_izin` varchar(20) NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `isi_surat` text NOT NULL,
  `id_ttd_pengaju` varchar(20) DEFAULT NULL,
  `status_surat` enum('menunggu_manajer','menunggu_hrd','disetujui','ditolak') NOT NULL DEFAULT 'menunggu_manajer',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_surat`),
  KEY `surat_izin_id_izin_foreign` (`id_izin`),
  KEY `surat_izin_id_ttd_pengaju_foreign` (`id_ttd_pengaju`),
  KEY `surat_izin_id_user_status_surat_index` (`id_user`,`status_surat`),
  CONSTRAINT `surat_izin_id_izin_foreign` FOREIGN KEY (`id_izin`) REFERENCES `pengajuan_izin` (`id_izin`) ON DELETE CASCADE,
  CONSTRAINT `surat_izin_id_ttd_pengaju_foreign` FOREIGN KEY (`id_ttd_pengaju`) REFERENCES `tanda_tangan` (`id_tanda_tangan`) ON DELETE SET NULL,
  CONSTRAINT `surat_izin_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanda_tangan`
--

DROP TABLE IF EXISTS `tanda_tangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanda_tangan` (
  `id_tanda_tangan` varchar(20) NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `file_ttd` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tanda_tangan`),
  KEY `tanda_tangan_id_user_is_active_index` (`id_user`,`is_active`),
  CONSTRAINT `tanda_tangan_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `sisa_cuti` int(11) NOT NULL DEFAULT 12,
  `foto` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_kantor` bigint(20) unsigned DEFAULT NULL,
  `id_divisi` bigint(20) unsigned DEFAULT NULL,
  `id_jabatan` bigint(20) unsigned DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `tgl_bergabung` date DEFAULT NULL,
  `is_face_registered` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nik_unique` (`nik`),
  KEY `users_id_jabatan_index` (`id_jabatan`),
  KEY `users_id_divisi_index` (`id_divisi`),
  KEY `users_id_kantor_index` (`id_kantor`),
  KEY `users_status_aktif_index` (`status_aktif`),
  CONSTRAINT `users_id_divisi_foreign` FOREIGN KEY (`id_divisi`) REFERENCES `divisi` (`id_divisi`) ON DELETE SET NULL,
  CONSTRAINT `users_id_jabatan_foreign` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON DELETE SET NULL,
  CONSTRAINT `users_id_kantor_foreign` FOREIGN KEY (`id_kantor`) REFERENCES `kantor` (`id_kantor`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-28 14:00:26
