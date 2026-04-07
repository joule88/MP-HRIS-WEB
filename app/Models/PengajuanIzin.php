<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id_izin
 * @property int|null $id_user
 * @property string $tanggal_mulai
 * @property string $tanggal_selesai
 * @property int|null $id_jenis_izin
 * @property string|null $alasan
 * @property string|null $bukti_file
 * @property int $id_status
 * @property string|null $alasan_penolakan
 * @property string $tanggal_diajukan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\JenisIzin|null $jenisIzin
 * @property-read \App\Models\StatusPengajuan $statusPengajuan
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereAlasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereAlasanPenolakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereBuktiFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereIdIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereIdJenisIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereIdStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereTanggalDiajukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereTanggalMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereTanggalSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanIzin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PengajuanIzin extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_izin';
    protected $primaryKey = 'id_izin';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $prefix = 'IZN-' . date('ym') . '-';
                $model->{$model->getKeyName()} = $prefix . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    protected $fillable = [
        'id_user',
        'id_jenis_izin',
        'id_status',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'bukti_file',
        'alasan_penolakan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function jenisIzin()
    {
        return $this->belongsTo(JenisIzin::class, 'id_jenis_izin', 'id_jenis_izin');
    }

    public function statusPengajuan()
    {
        return $this->belongsTo(StatusPengajuan::class, 'id_status', 'id_status');
    }

    public function suratIzin()
    {
        return $this->hasOne(SuratIzin::class, 'id_izin', 'id_izin');
    }
}
