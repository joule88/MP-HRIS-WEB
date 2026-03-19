<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_riwayat
 * @property int $id_user_1
 * @property int $id_jadwal_1
 * @property int $id_user_2
 * @property int $id_jadwal_2
 * @property string|null $keterangan
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $execAdmin
 * @property-read \App\Models\JadwalKerja $jadwal1
 * @property-read \App\Models\JadwalKerja $jadwal2
 * @property-read \App\Models\User $user1
 * @property-read \App\Models\User $user2
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereIdJadwal1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereIdJadwal2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereIdRiwayat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereIdUser1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereIdUser2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatTukarShift whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RiwayatTukarShift extends Model
{
    protected $table = 'riwayat_tukar_shift';
    protected $primaryKey = 'id_riwayat';

    protected $fillable = [
        'id_user_1',
        'id_jadwal_1',
        'id_user_2',
        'id_jadwal_2',
        'keterangan',
        'created_by',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'id_user_1', 'id');
    }

    public function jadwal1()
    {
        return $this->belongsTo(JadwalKerja::class, 'id_jadwal_1', 'id_jadwal');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'id_user_2', 'id');
    }

    public function jadwal2()
    {
        return $this->belongsTo(JadwalKerja::class, 'id_jadwal_2', 'id_jadwal');
    }

    public function execAdmin()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
