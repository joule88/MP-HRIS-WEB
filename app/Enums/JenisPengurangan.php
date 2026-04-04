<?php

namespace App\Enums;

class JenisPengurangan
{
    const DATANG_TERLAMBAT = 1;
    const PULANG_CEPAT_BIASA = 2;
    const TIDAK_HADIR_ALPHA = 3;
    const MASUK_SIANG_POIN = 4;
    const PULANG_CEPAT_POIN = 5;

    const LABELS = [
        self::DATANG_TERLAMBAT => 'Datang Terlambat',
        self::PULANG_CEPAT_BIASA => 'Pulang Cepat (Biasa)',
        self::TIDAK_HADIR_ALPHA => 'Tidak Hadir (Alpha)',
        self::MASUK_SIANG_POIN => 'Masuk Siang (Poin)',
        self::PULANG_CEPAT_POIN => 'Pulang Cepat (Poin)',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
