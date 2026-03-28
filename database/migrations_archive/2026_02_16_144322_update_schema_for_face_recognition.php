<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_face_registered')) {
                $table->tinyInteger('is_face_registered')->default(0)->after('password');
            }
        });

        if (!Schema::hasTable('data_wajah')) {
            Schema::create('data_wajah', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
                $table->string('path_model_yml')->nullable();
                $table->tinyInteger('is_verified')->default(0)->comment('0:Pending, 1:Approved, 2:Rejected');
                $table->timestamp('last_updated')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_face_registered')) {
                $table->dropColumn('is_face_registered');
            }
        });

        Schema::dropIfExists('data_wajah');
    }
};
