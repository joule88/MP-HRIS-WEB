<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            $table->string('path_model_yml')->nullable()->after('id_user');
        });
    }

    public function down(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            $table->dropColumn('path_model_yml');
        });
    }
};
