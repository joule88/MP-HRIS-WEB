<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            if (!Schema::hasColumn('pengumuman', 'foto')) {
                $table->string('foto')->nullable()->after('isi');
            }
            if (!Schema::hasColumn('pengumuman', 'lampiran')) {
                $table->string('lampiran')->nullable()->after('foto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropColumn(['foto', 'lampiran']);
        });
    }
};
