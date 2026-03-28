<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_surat', function (Blueprint $table) {
            $table->string('id_approval', 20)->primary();
            $table->string('id_surat', 20);
            $table->foreignId('id_approver')->constrained('users');
            $table->string('id_ttd_approver', 20)->nullable();
            $table->tinyInteger('tahap')->comment('1=Manajer, 2=HRD');
            $table->enum('status', ['disetujui', 'ditolak'])->default('disetujui');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_surat')->references('id_surat')->on('surat_izin')->cascadeOnDelete();
            $table->foreign('id_ttd_approver')->references('id_tanda_tangan')->on('tanda_tangan')->nullOnDelete();
            $table->unique(['id_surat', 'tahap']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_surat');
    }
};
