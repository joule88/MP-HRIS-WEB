<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (!Schema::hasTable('face_registrations')) {
            Schema::create('face_registrations', function (Blueprint $table) {
                $table->id('id_face_registration');
                $table->unsignedBigInteger('id_pegawai');
                $table->text('face_embedding')->nullable();
                $table->string('foto_pendaftaran', 255)->nullable();
                $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->foreign('id_pegawai')->references('id_pegawai')->on('pegawai')->onDelete('cascade');
                $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('face_registrations');
    }
};
