<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('hari_libur', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kantor')->nullable()->after('keterangan');
            $table->foreign('id_kantor')->references('id_kantor')->on('kantor')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('hari_libur', function (Blueprint $table) {
            $table->dropForeign(['id_kantor']);
            $table->dropColumn('id_kantor');
        });
    }
};
