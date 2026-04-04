<?php

namespace App\Enums;

class JenisIzin
{
    const SAKIT = 1;
    const CUTI = 2;
    const IZIN = 3;

    const LABELS = [
        self::SAKIT => 'Sakit',
        self::CUTI => 'Cuti',
        self::IZIN => 'Izin',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
