<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property int $id_jadwal
 * @property int|null $id_user
 * @property string|null $tanggal
 * @property int|null $id_shift
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PenggunaanPoin|null $penggunaanPoin
 * @property-read \App\Models\ShiftKerja|null $shift
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja whereIdJadwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja whereIdShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JadwalKerja whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class JadwalKerja extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kerja';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_user',
        'id_shift',
        'tanggal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function shift()
    {
        return $this->belongsTo(ShiftKerja::class, 'id_shift');
    }

    public function penggunaanPoin()
    {
        return $this->hasOne(PenggunaanPoin::class, 'id_user', 'id_user')
            ->whereColumn('tanggal_penggunaan', 'tanggal')
            ->where('id_status', 2);
    }

}
