<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
class JenisKompensasi extends Model
{
    protected $table = 'jenis_kompensasi';
    protected $primaryKey = 'id_kompensasi';

    const UANG = 1;
    const POIN = 2;
}
