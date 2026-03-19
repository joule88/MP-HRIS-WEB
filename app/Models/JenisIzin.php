<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
class JenisIzin extends Model
{
    protected $table = 'jenis_izin';
    protected $primaryKey = 'id_jenis_izin';
    public $timestamps = false;

    protected $fillable = ['id_jenis_izin', 'nama_izin'];
}
