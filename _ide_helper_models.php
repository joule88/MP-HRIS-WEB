<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
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
	class ApprovalSurat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_user
 * @property string|null $path_model_yml
 * @property int $is_verified 0:Pending, 1:Approved, 2:Rejected
 * @property string|null $last_updated
 * @property string|null $encoding_wajah
 * @property string $tanggal_latih
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereEncodingWajah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah wherePathModelYml($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereTanggalLatih($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataWajah whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DataWajah extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_penggunaan
 * @property int $id_poin_sumber
 * @property int $jumlah_diambil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PenggunaanPoin $penggunaan
 * @property-read \App\Models\PoinLembur $poinSumber
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereIdPenggunaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereIdPoinSumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereJumlahDiambil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetailPenggunaanPoin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DetailPenggunaanPoin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_divisi
 * @property string $nama_divisi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi whereNamaDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Divisi whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Divisi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tanggal
 * @property string $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HariLibur whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class HariLibur extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_jabatan
 * @property string $nama_jabatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereIdJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereNamaJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Jabatan extends \Eloquent {}
}

namespace App\Models{
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
	class JadwalKerja extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_jenis_izin
 * @property string $nama_izin
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin whereIdJenisIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin whereNamaIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisIzin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class JenisIzin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_kompensasi
 * @property string $nama_kompensasi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi whereIdKompensasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi whereNamaKompensasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisKompensasi whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class JenisKompensasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_pengurangan
 * @property string $nama_pengurangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PenggunaanPoin> $penggunaanPoin
 * @property-read int|null $penggunaan_poin_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan whereIdPengurangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan whereNamaPengurangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPengurangan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class JenisPengurangan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_kantor
 * @property string $nama_kantor
 * @property string|null $alamat
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property int $radius
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereIdKantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereNamaKantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kantor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Kantor extends \Eloquent {}
}

namespace App\Models{
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
	class Lembur extends \Eloquent {}
}

namespace App\Models{
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
	class PengajuanIzin extends \Eloquent {}
}

namespace App\Models{
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
	class PenggunaanPoin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_pengumuman
 * @property string|null $judul
 * @property string|null $isi
 * @property \Illuminate\Support\Carbon|null $tanggal
 * @property int|null $dibuat_oleh
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $pembuat
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereDibuatOleh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereIdPengumuman($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereIsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengumuman whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Pengumuman extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_permission
 * @property string $nama_permission
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereIdPermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereNamaPermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poin query()
 * @mixin \Eloquent
 */
	class Poin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_poin
 * @property int|null $id_user
 * @property int|null $jumlah_poin
 * @property int|null $sisa_poin
 * @property string|null $id_lembur
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon $tanggal
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property bool|null $is_fully_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lembur|null $lembur
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIdLembur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIdPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereIsFullyUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereJumlahPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereSisaPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoinLembur whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PoinLembur extends \Eloquent {}
}

namespace App\Models{
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
	class Presensi extends \Eloquent {}
}

namespace App\Models{
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
	class RiwayatTukarShift extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_role
 * @property string $nama_role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereIdRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereNamaRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
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
	class ShiftKerja extends \Eloquent {}
}

namespace App\Models{
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
	class StatusPengajuan extends \Eloquent {}
}

namespace App\Models{
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
	class SuratIzin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id_tanda_tangan
 * @property int $id_user
 * @property string $file_ttd
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan whereFileTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan whereIdTandaTangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TandaTangan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class TandaTangan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $nik
 * @property string $nama_lengkap
 * @property string $email
 * @property string|null $no_telp
 * @property string|null $alamat
 * @property string|null $email_verified_at
 * @property string $password
 * @property int $sisa_cuti
 * @property string|null $foto
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $id_kantor
 * @property int|null $id_divisi
 * @property int|null $id_jabatan
 * @property int $status_aktif
 * @property string|null $tgl_bergabung
 * @property int $is_face_registered
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\DataWajah|null $dataWajah
 * @property-read \App\Models\Divisi|null $divisi
 * @property-read \App\Models\Jabatan|null $jabatan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JadwalKerja> $jadwalKerja
 * @property-read int|null $jadwal_kerja_count
 * @property-read \App\Models\Kantor|null $kantor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lembur> $lemburs
 * @property-read int|null $lemburs_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdKantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsFaceRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNamaLengkap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSisaCuti($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatusAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTglBergabung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

