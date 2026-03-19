<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_penggunaan
 * @property int $id_poin_sumber
 * @property int $jumlah_diambil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PenggunaanPoin $penggunaan
 * @property-read \App\Models\PoinLembur $poinSumber
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereIdPenggunaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereIdPoinSumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereJumlahDiambil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DetailPenggunaanPoin extends Model
{
    use HasFactory;

    protected $table = 'detail_penggunaan_poin';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_penggunaan',
        'id_poin_sumber',
        'jumlah_diambil'
    ];

    public function penggunaan()
    {
        return $this->belongsTo(PenggunaanPoin::class, 'id_penggunaan', 'id_penggunaan');
    }

    public function poinSumber()
    {
        return $this->belongsTo(PoinLembur::class, 'id_poin_sumber', 'id_poin');
    }
}
