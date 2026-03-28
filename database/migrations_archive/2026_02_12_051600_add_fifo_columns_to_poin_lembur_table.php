<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('poin_lembur', function (Blueprint $table) {
            if (!Schema::hasColumn('poin_lembur', 'sisa_poin')) {
                $table->integer('sisa_poin')->nullable()->after('jumlah_poin');
            }
            if (!Schema::hasColumn('poin_lembur', 'expired_at')) {
                $table->date('expired_at')->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('poin_lembur', 'is_fully_used')) {
                $table->boolean('is_fully_used')->default(false)->after('expired_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poin_lembur', function (Blueprint $table) {
            $table->dropColumn(['sisa_poin', 'expired_at', 'is_fully_used']);
        });
    }
};
