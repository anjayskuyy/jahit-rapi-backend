<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_masuk',
        'metode',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
