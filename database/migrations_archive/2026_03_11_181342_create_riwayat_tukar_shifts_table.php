<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('riwayat_tukar_shift', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->foreignId('id_user_1')->constrained('users', 'id')->cascadeOnDelete();
            $table->unsignedBigInteger('id_jadwal_1');
            $table->foreign('id_jadwal_1')->references('id_jadwal')->on('jadwal_kerja')->cascadeOnDelete();
            $table->foreignId('id_user_2')->constrained('users', 'id')->cascadeOnDelete();
            $table->unsignedBigInteger('id_jadwal_2');
            $table->foreign('id_jadwal_2')->references('id_jadwal')->on('jadwal_kerja')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_tukar_shift');
    }
};
