<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('poin_lembur', function (Blueprint $table) {

            if (Schema::hasColumn('poin_lembur', 'id_penggunaan')) {

                try {
                    $table->dropForeign(['id_penggunaan']);
                } catch (\Exception $e) {

                }
                $table->dropColumn('id_penggunaan');
            }

            if (!Schema::hasColumn('poin_lembur', 'sisa_poin')) {
                $table->integer('sisa_poin')->after('jumlah_poin')->default(0);
            }
            if (!Schema::hasColumn('poin_lembur', 'expired_at')) {
                $table->date('expired_at')->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('poin_lembur', 'is_fully_used')) {
                $table->boolean('is_fully_used')->default(false)->after('expired_at');
            }
        });

        if (!Schema::hasTable('detail_penggunaan_poin')) {
            Schema::create('detail_penggunaan_poin', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_penggunaan')->constrained('penggunaan_poin')->onDelete('cascade');
                $table->foreignId('id_poin_sumber')->constrained('poin_lembur')->onDelete('cascade');
                $table->integer('jumlah_diambil');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {

    }
};
