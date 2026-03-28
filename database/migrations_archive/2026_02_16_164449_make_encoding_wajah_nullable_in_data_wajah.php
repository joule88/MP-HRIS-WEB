<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (Schema::hasColumn('data_wajah', 'encoding_wajah')) {
                $table->text('encoding_wajah')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (Schema::hasColumn('data_wajah', 'encoding_wajah')) {

            }
        });
    }
};
