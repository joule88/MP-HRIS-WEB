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
                $table->integer('sisa_poin')->after('jumlah_poin')->comment('Saldo aktif poin ini');
            }
            if (!Schema::hasColumn('poin_lembur', 'expired_at')) {
                $table->date('expired_at')->after('tanggal')->comment('Batas waktu penggunaan');
            }
            if (!Schema::hasColumn('poin_lembur', 'is_fully_used')) {
                $table->boolean('is_fully_used')->default(false)->after('expired_at')->comment('True jika sisa_poin 0');
            }
        });

        if (!Schema::hasTable('detail_penggunaan_poin')) {
            Schema::create('detail_penggunaan_poin', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_penggunaan')->constrained('penggunaan_poin', 'id_penggunaan')->onDelete('cascade');
                $table->foreignId('id_poin_sumber')->constrained('poin_lembur', 'id_poin')->onDelete('cascade');
                $table->integer('jumlah_diambil');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penggunaan_poin');
        Schema::table('poin_lembur', function (Blueprint $table) {

            if (!Schema::hasColumn('poin_lembur', 'id_penggunaan')) {
                $table->foreignId('id_penggunaan')->nullable()->constrained('penggunaan_poin', 'id_penggunaan');
            }

        });
    }
};
