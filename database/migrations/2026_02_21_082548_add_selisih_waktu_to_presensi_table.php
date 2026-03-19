<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->time('waktu_terlambat')->nullable()->after('keterangan_pulang');
            $table->time('waktu_masuk_awal')->nullable()->after('waktu_terlambat');
            $table->time('waktu_pulang_awal')->nullable()->after('waktu_masuk_awal');
            $table->time('waktu_pulang_akhir')->nullable()->after('waktu_pulang_awal');
        });
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn([
                'waktu_terlambat',
                'waktu_masuk_awal',
                'waktu_pulang_awal',
                'waktu_pulang_akhir'
            ]);
        });
    }
};
