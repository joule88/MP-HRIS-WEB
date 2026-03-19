<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            if (!Schema::hasColumn('data_wajah', 'is_verified')) {
                $table->tinyInteger('is_verified')->default(0)->comment('0:Pending, 1:Approved, 2:Rejected')->after('path_model_yml');
            }
            if (!Schema::hasColumn('data_wajah', 'last_updated')) {
                $table->timestamp('last_updated')->nullable()->after('is_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_wajah', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'last_updated']);
        });
    }
};
