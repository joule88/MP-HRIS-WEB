<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_libur';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'id_kantor',
    ];

    public function kantor()
    {
        return $this->belongsTo(Kantor::class, 'id_kantor');
    }
}
