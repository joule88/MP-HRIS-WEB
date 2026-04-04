<?php

namespace App\Enums;

class JenisKompensasi
{
    const UANG_LEMBUR = 1;
    const TAMBAHAN_POIN = 2;

    const LABELS = [
        self::UANG_LEMBUR => 'Uang Lembur',
        self::TAMBAHAN_POIN => 'Tambahan Poin',
    ];

    public static function label(int $id): string
    {
        return self::LABELS[$id] ?? 'Unknown';
    }
}
