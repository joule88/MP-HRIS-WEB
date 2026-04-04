<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (!Schema::hasColumn('data_wajah', 'path_model_pkl')) {
                $table->string('path_model_pkl')->nullable()->after('path_model_yml');
            }
            if (!Schema::hasColumn('data_wajah', 'path_scaler_pkl')) {
                $table->string('path_scaler_pkl')->nullable()->after('path_model_pkl');
            }
            if (!Schema::hasColumn('data_wajah', 'path_video')) {
                $table->string('path_video')->nullable()->after('path_scaler_pkl');
            }
            if (!Schema::hasColumn('data_wajah', 'jumlah_frame')) {
                $table->integer('jumlah_frame')->nullable()->after('path_video');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (Schema::hasColumn('data_wajah', 'path_model_pkl')) {
                $table->dropColumn('path_model_pkl');
            }
            if (Schema::hasColumn('data_wajah', 'path_scaler_pkl')) {
                $table->dropColumn('path_scaler_pkl');
            }
            if (Schema::hasColumn('data_wajah', 'path_video')) {
                $table->dropColumn('path_video');
            }
            if (Schema::hasColumn('data_wajah', 'jumlah_frame')) {
                $table->dropColumn('jumlah_frame');
            }
        });
    }
};
