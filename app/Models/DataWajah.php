<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_user
 * @property string|null $path_video
 * @property int|null $jumlah_frame
 * @property int $is_verified 0:Pending, 1:Approved, 2:Rejected
 * @property string|null $last_updated
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
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