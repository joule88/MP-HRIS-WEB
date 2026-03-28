<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surat_izin', function (Blueprint $table) {
            $table->string('id_surat', 20)->primary();
            $table->string('id_izin', 20);
            $table->foreignId('id_user')->constrained('users');
            $table->string('nomor_surat', 50);
            $table->text('isi_surat');
            $table->string('id_ttd_pengaju', 20)->nullable();
            $table->enum('status_surat', ['menunggu_manajer', 'menunggu_hrd', 'disetujui', 'ditolak'])->default('menunggu_manajer');
            $table->timestamps();

            $table->foreign('id_izin')->references('id_izin')->on('pengajuan_izin')->cascadeOnDelete();
            $table->foreign('id_ttd_pengaju')->references('id_tanda_tangan')->on('tanda_tangan')->nullOnDelete();
            $table->index(['id_user', 'status_surat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_izin');
    }
};
