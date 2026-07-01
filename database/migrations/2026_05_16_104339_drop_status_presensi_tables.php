<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropForeign(['id_status']);
            $table->dropForeign(['id_validasi']);
        });

        Schema::dropIfExists('status_presensi');
        Schema::dropIfExists('status_validasi_presensi');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tables and constraints if needed, but omitted since they are replaced by Enum
    }
};
