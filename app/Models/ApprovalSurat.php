<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $id_approval
 * @property string $id_surat
 * @property int $id_approver
 * @property string|null $id_ttd_approver
 * @property int $tahap 1=Manajer, 2=HRD
 * @property string $status
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $approver
 * @property-read \App\Models\SuratIzin $suratIzin
 * @property-read \App\Models\TandaTangan|null $tandaTanganApprover
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereIdApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereIdApprover($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereIdSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereIdTtdApprover($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereTahap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalSurat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ApprovalSurat extends Model
{
    protected $table = 'approval_surat';
    protected $primaryKey = 'id_approval';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_surat',
        'id_approver',
        'id_ttd_approver',
        'tahap',
        'status',
        'catatan',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $prefix = 'APR-' . date('ym') . '-';
                $model->{$model->getKeyName()} = $prefix . strtoupper(Str::random(5));
            }
        });
    }

    public function suratIzin()
    {
        return $this->belongsTo(SuratIzin::class, 'id_surat', 'id_surat');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'id_approver');
    }

    public function tandaTanganApprover()
    {
        return $this->belongsTo(TandaTangan::class, 'id_ttd_approver', 'id_tanda_tangan');
    }
}
