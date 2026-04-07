<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'email',
        'no_telp',
        'alamat',
        'password',
        'foto',
        'id_kantor',
        'id_divisi',
        'id_jabatan',
        'status_aktif',
        'tgl_bergabung',
        'is_face_registered',
        'sisa_cuti',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'detail_user_roles', 'id_user', 'id_role');
    }

    public function hasPermission($permissionSlug): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug);
        })->exists();
    }

    public function isGlobalAdmin(): bool
    {
        return $this->roles->contains(function ($role) {
            return strtolower($role->nama_role) === 'hrd';
        });
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles->contains(function ($role) {
            return strtolower($role->nama_role) === 'super_admin';
        });
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'id_kantor', 'id_kantor');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id_divisi');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function jadwalKerja(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JadwalKerja::class, 'id_user', 'id');
    }

    public function dataWajah()
    {
        return $this->hasOne(DataWajah::class, 'id_user', 'id');
    }

    public function lemburs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lembur::class, 'id_user', 'id');
    }

    public function notifikasi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_user', 'id');
    }

    public function scopeBukanHrd($query)
    {
        return $query->whereDoesntHave('roles', function ($q) {
            $q->where('nama_role', 'hrd');
        });
    }
}
