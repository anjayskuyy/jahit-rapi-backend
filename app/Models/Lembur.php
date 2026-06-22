<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'jam' => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
