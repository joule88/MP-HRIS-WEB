<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('id_jabatan');
            $table->index('id_divisi');
            $table->index('id_kantor');
            $table->index('status_aktif');
        });

        Schema::table('presensi', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('id_user');
            $table->index('id_status');
            $table->index('id_validasi');
            $table->index(['id_user', 'tanggal']);
        });

        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->index(['id_user', 'tanggal']);
        });

        Schema::table('penggunaan_poin', function (Blueprint $table) {
            $table->index(['id_user', 'tanggal_penggunaan']);
            $table->index('id_status');
        });

        Schema::table('pengajuan_izin', function (Blueprint $table) {
            $table->index('id_status');
            $table->index('id_user');
        });

        Schema::table('lembur', function (Blueprint $table) {
            $table->index('id_status');
            $table->index('id_user');
        });

        Schema::table('poin_lembur', function (Blueprint $table) {
            $table->index('id_user');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['id_jabatan']);
            $table->dropIndex(['id_divisi']);
            $table->dropIndex(['id_kantor']);
            $table->dropIndex(['status_aktif']);
        });

        Schema::table('presensi', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['id_user']);
            $table->dropIndex(['id_status']);
            $table->dropIndex(['id_validasi']);
            $table->dropIndex(['id_user', 'tanggal']);
        });

        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->dropIndex(['id_user', 'tanggal']);
        });

        Schema::table('penggunaan_poin', function (Blueprint $table) {
            $table->dropIndex(['id_user', 'tanggal_penggunaan']);
            $table->dropIndex(['id_status']);
        });

        Schema::table('pengajuan_izin', function (Blueprint $table) {
            $table->dropIndex(['id_status']);
            $table->dropIndex(['id_user']);
        });

        Schema::table('lembur', function (Blueprint $table) {
            $table->dropIndex(['id_status']);
            $table->dropIndex(['id_user']);
        });

        Schema::table('poin_lembur', function (Blueprint $table) {
            $table->dropIndex(['id_user']);
            $table->dropIndex(['tanggal']);
        });
    }
};
