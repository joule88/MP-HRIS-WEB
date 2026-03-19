-- Migration: Add keterangan_luar_radius to presensi table
-- File: database/migrations/manual_add_keterangan_luar_radius.sql
-- Author: MPG HRIS Team
-- Created: 2026-02-06

ALTER TABLE `presensi` 
ADD COLUMN `keterangan_luar_radius` TEXT NULL AFTER `id_validasi`;

-- Deskripsi: Kolom untuk menyimpan keterangan/alasan ketika user melakukan presensi di luar radius kantor
