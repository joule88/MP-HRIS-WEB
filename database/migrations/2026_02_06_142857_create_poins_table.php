<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('poin', function (Blueprint $table) {
            $table->id('id_poin');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->integer('jumlah_poin');
            $table->string('sumber')->default('bonus');
            $table->date('tgl_kadaluarsa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poins');
    }
};
