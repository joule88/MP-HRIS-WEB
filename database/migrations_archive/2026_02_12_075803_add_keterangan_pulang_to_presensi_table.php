<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (Schema::hasTable('presensi') && !Schema::hasColumn('presensi', 'keterangan_pulang')) {
            Schema::table('presensi', function (Blueprint $table) {
                $table->string('keterangan_pulang', 150)->nullable()->after('alasan_telat');
            });
        }
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn('keterangan_pulang');
        });
    }
};
