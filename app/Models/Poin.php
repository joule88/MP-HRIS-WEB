<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poin query()
 * @mixin \Eloquent
 */
class Poin extends Model
{
    protected $table = 'poin';
    protected $primaryKey = 'id_poin';

    protected $fillable = [
        'id_user',
        'jumlah_poin',
        'sumber',
        'tgl_kadaluarsa',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
