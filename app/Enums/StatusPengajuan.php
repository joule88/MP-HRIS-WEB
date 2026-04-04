<?php

namespace App\Enums;

class StatusPengajuan
{
    const PENDING = 1;
    const DISETUJUI = 2;
    const DITOLAK = 3;

    const LABELS = [
        self::PENDING => 'Pending',
        self::DISETUJUI => 'Disetujui',
        self::DITOLAK => 'Ditolak',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
