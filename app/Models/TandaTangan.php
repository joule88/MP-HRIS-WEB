<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
class TandaTangan extends Model
{
    protected $table = 'tanda_tangan';
    protected $primaryKey = 'id_tanda_tangan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_user', 'file_ttd', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $prefix = 'TTD-' . date('ym') . '-';
                $model->{$model->getKeyName()} = $prefix . strtoupper(Str::random(5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
