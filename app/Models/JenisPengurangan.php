<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
class JenisPengurangan extends Model
{
    use HasFactory;

    protected $table = 'jenis_pengurangan';
    protected $primaryKey = 'id_pengurangan';

    protected $fillable = [
        'nama_pengurangan'
    ];

    public function penggunaanPoin()
    {
        return $this->hasMany(PenggunaanPoin::class, 'id_pengurangan');
    }
}
