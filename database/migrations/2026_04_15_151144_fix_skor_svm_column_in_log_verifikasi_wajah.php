<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_verifikasi_wajah', function (Blueprint $table) {
            // decimal(5,4) hanya bisa simpan max 9.9999
            // SVM decision function bisa menghasilkan nilai sangat besar/kecil
            // Ganti ke decimal(10,4) → bisa simpan hingga ±999999.9999
            $table->decimal('skor_svm', 10, 4)->nullable()->change();
            $table->decimal('skor_kepercayaan', 10, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('log_verifikasi_wajah', function (Blueprint $table) {
            $table->decimal('skor_svm', 5, 4)->nullable()->change();
            $table->decimal('skor_kepercayaan', 5, 4)->nullable()->change();
        });
    }
};
