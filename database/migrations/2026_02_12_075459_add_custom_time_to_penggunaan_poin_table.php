<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('penggunaan_poin', function (Blueprint $table) {
            if (!Schema::hasColumn('penggunaan_poin', 'jam_masuk_custom')) {
                $table->time('jam_masuk_custom')->nullable()->after('jumlah_poin');
            }
            if (!Schema::hasColumn('penggunaan_poin', 'jam_pulang_custom')) {
                $table->time('jam_pulang_custom')->nullable()->after('jam_masuk_custom');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penggunaan_poin', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk_custom', 'jam_pulang_custom']);
        });
    }
};
