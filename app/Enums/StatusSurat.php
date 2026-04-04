<?php

namespace App\Enums;

class StatusSurat
{
    const MENUNGGU_MANAJER = 'menunggu_manajer';
    const MENUNGGU_HRD = 'menunggu_hrd';
    const DISETUJUI = 'disetujui';
    const DITOLAK = 'ditolak';

    const LABELS = [
        self::MENUNGGU_MANAJER => 'Menunggu Manajer',
        self::MENUNGGU_HRD => 'Menunggu HRD',
        self::DISETUJUI => 'Disetujui',
        self::DITOLAK => 'Ditolak',
    ];

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? 'Unknown';
    }
}
