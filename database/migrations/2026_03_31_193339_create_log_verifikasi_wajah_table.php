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
                $table->decimal('confidence', 5, 4)->nullable();
                $table->decimal('svm_confidence', 5, 4)->nullable();
                $table->decimal('normalized_distance', 8, 4)->nullable();
                $table->string('verification_status', 30)->nullable();
                $table->boolean('is_match')->default(false);
                $table->decimal('blur_score', 6, 1)->nullable();
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
