<?php

namespace App\Enums;

class StatusVerifikasiWajah
{
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    const LABELS = [
        self::PENDING => 'Pending',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
