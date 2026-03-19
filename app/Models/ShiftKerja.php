<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_shift
 * @property string|null $nama_shift
 * @property string|null $jam_mulai
 * @property string|null $jam_selesai
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JadwalKerja> $jadwalKerja
 * @property-read int|null $jadwal_kerja_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja whereIdShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja whereJamMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja whereJamSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja whereNamaShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftKerja whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShiftKerja extends Model
{
    use HasFactory;

    protected $table = 'shift_kerja';
    protected $primaryKey = 'id_shift';

    protected $fillable = [
        'nama_shift',
        'jam_mulai',
        'jam_selesai',
    ];

    public function jadwalKerja(): HasMany
    {
        return $this->hasMany(JadwalKerja::class, 'id_shift', 'id_shift');
    }
}