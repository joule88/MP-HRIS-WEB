<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id_penggunaan
 * @property int|null $id_user
 * @property \Illuminate\Support\Carbon $tanggal_penggunaan
 * @property int|null $jumlah_poin
 * @property string|null $jam_masuk_custom
 * @property string|null $jam_pulang_custom
 * @property int|null $id_pengurangan
 * @property int $id_status
 * @property string|null $alasan_penolakan
 * @property \Illuminate\Support\Carbon $tanggal_diajukan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\JenisPengurangan|null $jenisPengurangan
 * @property-read \App\Models\StatusPengajuan $status
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereAlasanPenolakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereIdPenggunaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereIdPengurangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereIdStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereJamMasukCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereJamPulangCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereJumlahPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereTanggalDiajukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereTanggalPenggunaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenggunaanPoin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PenggunaanPoin extends Model
{
    use HasFactory;

    protected $table = 'penggunaan_poin';
    protected $primaryKey = 'id_penggunaan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $prefix = 'PNP-' . date('ym') . '-';
                $model->{$model->getKeyName()} = $prefix . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    protected $fillable = [
        'id_user',
        'tanggal_penggunaan',
        'jumlah_poin',
        'id_pengurangan',
        'jam_masuk_custom',
        'jam_pulang_custom',
        'id_status',
        'alasan_penolakan',
        'tanggal_diajukan'
    ];

    protected $casts = [
        'tanggal_penggunaan' => 'date',
        'tanggal_diajukan' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function jenisPengurangan()
    {
        return $this->belongsTo(JenisPengurangan::class, 'id_pengurangan');
    }

    public function status()
    {
        return $this->belongsTo(StatusPengajuan::class, 'id_status', 'id_status');
    }
}
