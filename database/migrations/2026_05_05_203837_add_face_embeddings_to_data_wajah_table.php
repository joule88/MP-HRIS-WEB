<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (!Schema::hasColumn('data_wajah', 'face_embeddings')) {
                $table->json('face_embeddings')->nullable()->after('jumlah_frame');
            }
            if (!Schema::hasColumn('data_wajah', 'jumlah_embedding')) {
                $table->integer('jumlah_embedding')->nullable()->after('face_embeddings');
            }
            if (!Schema::hasColumn('data_wajah', 'embedding_generated_at')) {
                $table->timestamp('embedding_generated_at')->nullable()->after('jumlah_embedding');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (Schema::hasColumn('data_wajah', 'face_embeddings')) {
                $table->dropColumn('face_embeddings');
            }
            if (Schema::hasColumn('data_wajah', 'jumlah_embedding')) {
                $table->dropColumn('jumlah_embedding');
            }
            if (Schema::hasColumn('data_wajah', 'embedding_generated_at')) {
                $table->dropColumn('embedding_generated_at');
            }
        });
    }
};
