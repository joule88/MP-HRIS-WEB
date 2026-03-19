<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {

    public function up(): void
    {

        DB::statement('ALTER TABLE poin_lembur DROP FOREIGN KEY poin_lembur_id_lembur_foreign;');
        DB::statement('ALTER TABLE detail_penggunaan_poin DROP FOREIGN KEY detail_penggunaan_poin_id_penggunaan_foreign;');

        DB::statement('ALTER TABLE pengajuan_izin MODIFY id_izin VARCHAR(20) NOT NULL;');

        DB::statement('ALTER TABLE lembur MODIFY id_lembur VARCHAR(20) NOT NULL;');
        DB::statement('ALTER TABLE poin_lembur MODIFY id_lembur VARCHAR(20) NULL;');

        DB::statement('ALTER TABLE penggunaan_poin MODIFY id_penggunaan VARCHAR(20) NOT NULL;');
        DB::statement('ALTER TABLE detail_penggunaan_poin MODIFY id_penggunaan VARCHAR(20) NOT NULL;');

        $this->convertExistingData();

        DB::statement('ALTER TABLE poin_lembur ADD CONSTRAINT poin_lembur_id_lembur_foreign FOREIGN KEY (id_lembur) REFERENCES lembur (id_lembur) ON DELETE SET NULL;');

        DB::statement('ALTER TABLE detail_penggunaan_poin ADD CONSTRAINT detail_penggunaan_poin_id_penggunaan_foreign FOREIGN KEY (id_penggunaan) REFERENCES penggunaan_poin (id_penggunaan) ON DELETE CASCADE;');
    }

    private function convertExistingData()
    {

        $izinRecords = DB::table('pengajuan_izin')->get();
        foreach ($izinRecords as $izin) {
            if (is_numeric($izin->id_izin)) {
                $createdDate = $izin->created_at ? date('ym', strtotime($izin->created_at)) : date('ym');
                $newId = 'IZN-' . $createdDate . '-' . strtoupper(Str::random(5));

                DB::table('pengajuan_izin')
                    ->where('id_izin', $izin->id_izin)
                    ->update(['id_izin' => $newId]);
            }
        }

        $lemburRecords = DB::table('lembur')->get();
        foreach ($lemburRecords as $lembur) {
            if (is_numeric($lembur->id_lembur)) {
                $createdDate = $lembur->created_at ? date('ym', strtotime($lembur->created_at)) : date('ym');
                $newId = 'LMB-' . $createdDate . '-' . strtoupper(Str::random(5));

                DB::table('poin_lembur')
                    ->where('id_lembur', $lembur->id_lembur)
                    ->update(['id_lembur' => $newId]);

                DB::table('lembur')
                    ->where('id_lembur', $lembur->id_lembur)
                    ->update(['id_lembur' => $newId]);
            }
        }

        $penggunaanRecords = DB::table('penggunaan_poin')->get();
        foreach ($penggunaanRecords as $penggunaan) {
            if (is_numeric($penggunaan->id_penggunaan)) {
                $createdDate = $penggunaan->created_at ? date('ym', strtotime($penggunaan->created_at)) : date('ym');
                $newId = 'PNP-' . $createdDate . '-' . strtoupper(Str::random(5));

                DB::table('detail_penggunaan_poin')
                    ->where('id_penggunaan', $penggunaan->id_penggunaan)
                    ->update(['id_penggunaan' => $newId]);

                DB::table('penggunaan_poin')
                    ->where('id_penggunaan', $penggunaan->id_penggunaan)
                    ->update(['id_penggunaan' => $newId]);
            }
        }
    }

    public function down(): void
    {

        DB::statement('ALTER TABLE poin_lembur DROP FOREIGN KEY poin_lembur_id_lembur_foreign;');
        DB::statement('ALTER TABLE detail_penggunaan_poin DROP FOREIGN KEY detail_penggunaan_poin_id_penggunaan_foreign;');

        DB::statement('ALTER TABLE pengajuan_izin MODIFY id_izin BIGINT UNSIGNED AUTO_INCREMENT;');

        DB::statement('ALTER TABLE lembur MODIFY id_lembur BIGINT UNSIGNED AUTO_INCREMENT;');
        DB::statement('ALTER TABLE poin_lembur MODIFY id_lembur BIGINT UNSIGNED NULL;');

        DB::statement('ALTER TABLE penggunaan_poin MODIFY id_penggunaan BIGINT UNSIGNED AUTO_INCREMENT;');
        DB::statement('ALTER TABLE detail_penggunaan_poin MODIFY id_penggunaan BIGINT UNSIGNED NOT NULL;');

        DB::statement('ALTER TABLE poin_lembur ADD CONSTRAINT poin_lembur_id_lembur_foreign FOREIGN KEY (id_lembur) REFERENCES lembur (id_lembur) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE detail_penggunaan_poin ADD CONSTRAINT detail_penggunaan_poin_id_penggunaan_foreign FOREIGN KEY (id_penggunaan) REFERENCES penggunaan_poin (id_penggunaan) ON DELETE CASCADE;');
    }
};
