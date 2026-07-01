-- =====================================================
-- Cleanup kolom deprecated di tabel data_wajah
-- Jalankan di MySQL Workbench / phpMyAdmin / CLI
-- =====================================================

USE `db_mpg`;

ALTER TABLE `data_wajah`
  DROP COLUMN IF EXISTS `path_model_yml`,
  DROP COLUMN IF EXISTS `path_model_pkl`,
  DROP COLUMN IF EXISTS `path_scaler_pkl`,
  DROP COLUMN IF EXISTS `face_embeddings`,
  DROP COLUMN IF EXISTS `jumlah_embedding`,
  DROP COLUMN IF EXISTS `embedding_generated_at`,
  DROP COLUMN IF EXISTS `encoding_wajah`,
  DROP COLUMN IF EXISTS `tanggal_latih`;
