<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tanda_tangan', function (Blueprint $table) {
            $table->string('id_tanda_tangan', 20)->primary();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->string('file_ttd');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['id_user', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_tangan');
    }
};
