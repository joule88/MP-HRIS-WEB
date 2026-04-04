<?php

namespace App\Enums;

class StatusPresensi
{
    const TEPAT_WAKTU = 1;
    const TERLAMBAT = 2;
    const IZIN = 3;
    const SAKIT = 4;
    const ALPHA = 5;

    const LABELS = [
        self::TEPAT_WAKTU => 'Tepat Waktu',
        self::TERLAMBAT => 'Terlambat',
        self::IZIN => 'Izin',
        self::SAKIT => 'Sakit',
        self::ALPHA => 'Alpha',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
