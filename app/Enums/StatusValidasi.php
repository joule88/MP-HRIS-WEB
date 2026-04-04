<?php

namespace App\Enums;

class StatusValidasi
{
    const VALID = 1;
    const PENDING = 2;
    const DITOLAK = 3;

    const LABELS = [
        self::VALID => 'Valid',
        self::PENDING => 'Pending',
        self::DITOLAK => 'Ditolak',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
