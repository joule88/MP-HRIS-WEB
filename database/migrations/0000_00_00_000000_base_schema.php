<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('base_schema.sql');
        if (File::exists($path)) {
            $sql = File::get($path);
            
            // Remove UTF-8 BOM if present
            if (str_starts_with($sql, "\xEF\xBB\xBF")) {
                $sql = substr($sql, 3);
            }
            
            // Also handle UTF-16LE just in case mysqldump was redirected directly in Powershell
            // and we read raw bytes (UTF-16LE BOM is FF FE)
            if (str_starts_with($sql, "\xFF\xFE")) {
                $sql = mb_convert_encoding(substr($sql, 2), 'UTF-8', 'UTF-16LE');
            }

            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        // 
    }
};
