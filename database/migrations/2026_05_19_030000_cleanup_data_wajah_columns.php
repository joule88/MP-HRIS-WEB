<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            $columnsToDrop = [
                'path_model_yml',
                'encoding_wajah',
                'tanggal_latih',
                'path_model_pkl',
                'path_scaler_pkl',
                'face_embeddings',
                'jumlah_embedding',
                'embedding_generated_at',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('data_wajah', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (!Schema::hasColumn('data_wajah', 'path_model_yml')) {
                $table->string('path_model_yml')->nullable();
            }
            if (!Schema::hasColumn('data_wajah', 'encoding_wajah')) {
                $table->text('encoding_wajah')->nullable();
            }
            if (!Schema::hasColumn('data_wajah', 'tanggal_latih')) {
                $table->timestamp('tanggal_latih')->useCurrent();
            }
            if (!Schema::hasColumn('data_wajah', 'path_model_pkl')) {
                $table->string('path_model_pkl')->nullable();
            }
            if (!Schema::hasColumn('data_wajah', 'path_scaler_pkl')) {
                $table->string('path_scaler_pkl')->nullable();
            }
            if (!Schema::hasColumn('data_wajah', 'face_embeddings')) {
                $table->json('face_embeddings')->nullable();
            }
            if (!Schema::hasColumn('data_wajah', 'jumlah_embedding')) {
                $table->integer('jumlah_embedding')->nullable();
            }
            if (!Schema::hasColumn('data_wajah', 'embedding_generated_at')) {
                $table->timestamp('embedding_generated_at')->nullable();
            }
        });
    }
};
