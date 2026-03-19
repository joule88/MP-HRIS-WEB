<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_poin
 * @property int|null $id_user
 * @property int|null $jumlah_poin
 * @property int|null $sisa_poin
 * @property string|null $id_lembur
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon $tanggal
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property bool|null $is_fully_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lembur|null $lembur
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIdLembur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIdPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIsFullyUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereJumlahPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereSisaPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PoinLembur extends Model
{
    use HasFactory;

    protected $table = 'poin_lembur';
    protected $primaryKey = 'id_poin';

    protected $fillable = [
        'id_user',
        'id_lembur',
        'jumlah_poin',
        'tanggal',
        'keterangan',
        'sisa_poin',
        'expired_at',
        'is_fully_used'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'expired_at' => 'date',
        'is_fully_used' => 'boolean',
    ];

    public function lembur()
    {
        return $this->belongsTo(Lembur::class, 'id_lembur');
    }
}