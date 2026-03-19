<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_status
 * @property string $nama_status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan whereIdStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan whereNamaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusPengajuan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StatusPengajuan extends Model
{
    protected $table = 'status_pengajuan';
    protected $primaryKey = 'id_status';
    public $timestamps = false;

    protected $fillable = ['id_status', 'nama_status'];
}
