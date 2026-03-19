<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

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
class DataWajah extends Model
{
    protected $table = 'data_wajah';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}