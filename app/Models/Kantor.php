<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
class Kantor extends Model
{
    use HasFactory;

    protected $table = 'kantor';
    protected $primaryKey = 'id_kantor';

    protected $fillable = [
        'nama_kantor',
        'tipe',
        'alamat',
        'latitude',
        'longitude',
        'radius'
    ];

    public function users()
    {

        return $this->hasMany(User::class, 'id_kantor');
    }
}
