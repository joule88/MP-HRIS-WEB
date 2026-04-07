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
        if (!Schema::hasTable('log_verifikasi_wajah')) {
            Schema::create('log_verifikasi_wajah', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_user');
                $table->decimal('skor_kepercayaan', 5, 4)->nullable();
                $table->decimal('skor_svm', 5, 4)->nullable();
                $table->decimal('jarak_normalisasi', 8, 4)->nullable();
                $table->string('status_verifikasi', 30)->nullable();
                $table->boolean('apakah_cocok')->default(false);
                $table->decimal('skor_blur', 6, 1)->nullable();
                $table->string('tipe', 10)->default('presensi');
                $table->timestamps();

                $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
                $table->index(['id_user', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_verifikasi_wajah');
    }
};
