<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_presensi
 * @property int|null $id_user
 * @property string $tanggal
 * @property string|null $jam_masuk
 * @property string|null $jam_pulang
 * @property numeric|null $lat_masuk
 * @property numeric|null $lon_masuk
 * @property numeric|null $lat_pulang
 * @property numeric|null $lon_pulang
 * @property string|null $foto_wajah_masuk
 * @property string|null $foto_wajah_pulang
 * @property int|null $id_status
 * @property string|null $alasan_telat
 * @property string|null $keterangan_pulang
 * @property string|null $waktu_terlambat
 * @property string|null $waktu_masuk_awal
 * @property string|null $waktu_pulang_awal
 * @property string|null $waktu_pulang_akhir
 * @property int $verifikasi_wajah
 * @property int $id_validasi
 * @property string|null $keterangan_luar_radius
 * @property string|null $alasan_penolakan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereAlasanPenolakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereAlasanTelat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereFotoWajahMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereFotoWajahPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereIdPresensi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereIdStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereIdValidasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereJamPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereKeteranganLuarRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereKeteranganPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereLatMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereLatPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereLonMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereLonPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereVerifikasiWajah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereWaktuMasukAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereWaktuPulangAkhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereWaktuPulangAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereWaktuTerlambat($value)
 * @mixin \Eloquent
 */
class Presensi extends Model
{

    protected $table = 'presensi';
    protected $primaryKey = 'id_presensi';

    protected $fillable = [
        'id_user',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'lat_masuk',
        'lon_masuk',
        'lat_pulang',
        'lon_pulang',
        'foto_wajah_masuk',
        'foto_wajah_pulang',
        'id_status',
        'alasan_telat',
        'verifikasi_wajah',
        'id_validasi',
        'alasan_penolakan',
        'keterangan_luar_radius',
        'keterangan_pulang',
        'waktu_terlambat',
        'waktu_masuk_awal',
        'waktu_pulang_awal',
        'waktu_pulang_akhir'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}