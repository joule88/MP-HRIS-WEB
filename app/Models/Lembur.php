<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id_lembur
 * @property int|null $id_user
 * @property \Illuminate\Support\Carbon $tanggal_lembur
 * @property string $jam_mulai
 * @property string $jam_selesai
 * @property int|null $durasi_menit
 * @property string|null $keterangan
 * @property int|null $jumlah_poin
 * @property int|null $id_kompensasi
 * @property int $id_status
 * @property string|null $alasan_penolakan
 * @property \Illuminate\Support\Carbon $tanggal_diajukan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\JenisKompensasi|null $kompensasi
 * @property-read \App\Models\StatusPengajuan $status
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereAlasanPenolakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereDurasiMenit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereIdKompensasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereIdLembur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereIdStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereJamMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereJamSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereJumlahPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereTanggalDiajukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereTanggalLembur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lembur whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';
    protected $primaryKey = 'id_lembur';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $prefix = 'LMB-' . date('ym') . '-';
                $model->{$model->getKeyName()} = $prefix . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    protected $fillable = [
        'id_user',
        'tanggal_lembur',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'keterangan',
        'jumlah_poin',
        'id_kompensasi',
        'id_status',
        'alasan_penolakan',
        'tanggal_diajukan'
    ];

    protected $casts = [
        'tanggal_lembur' => 'date',
        'tanggal_diajukan' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function kompensasi()
    {
        return $this->belongsTo(JenisKompensasi::class, 'id_kompensasi');
    }

    public function status()
    {
        return $this->belongsTo(StatusPengajuan::class, 'id_status');
    }
}