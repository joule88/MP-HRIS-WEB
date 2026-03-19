<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $id_surat
 * @property string $id_izin
 * @property int $id_user
 * @property string $nomor_surat
 * @property string $isi_surat
 * @property string|null $id_ttd_pengaju
 * @property string $status_surat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ApprovalSurat|null $approvalHrd
 * @property-read \App\Models\ApprovalSurat|null $approvalManajer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalSurat> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\PengajuanIzin $pengajuanIzin
 * @property-read \App\Models\TandaTangan|null $tandaTanganPengaju
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereIdIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereIdSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereIdTtdPengaju($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereIsiSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereNomorSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereStatusSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratIzin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SuratIzin extends Model
{
    protected $table = 'surat_izin';
    protected $primaryKey = 'id_surat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_izin',
        'id_user',
        'nomor_surat',
        'isi_surat',
        'id_ttd_pengaju',
        'status_surat',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $prefix = 'SRT-' . date('ym') . '-';
                $model->{$model->getKeyName()} = $prefix . strtoupper(Str::random(5));
            }

            if (empty($model->nomor_surat)) {
                $count = static::whereYear('created_at', date('Y'))->count() + 1;
                $model->nomor_surat = sprintf('%03d/MPG/IZN/%s', $count, date('Y'));
            }
        });
    }

    public function pengajuanIzin()
    {
        return $this->belongsTo(PengajuanIzin::class, 'id_izin', 'id_izin');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function tandaTanganPengaju()
    {
        return $this->belongsTo(TandaTangan::class, 'id_ttd_pengaju', 'id_tanda_tangan');
    }

    public function approvals()
    {
        return $this->hasMany(ApprovalSurat::class, 'id_surat', 'id_surat')->orderBy('tahap');
    }

    public function approvalManajer()
    {
        return $this->hasOne(ApprovalSurat::class, 'id_surat', 'id_surat')->where('tahap', 1);
    }

    public function approvalHrd()
    {
        return $this->hasOne(ApprovalSurat::class, 'id_surat', 'id_surat')->where('tahap', 2);
    }
}
