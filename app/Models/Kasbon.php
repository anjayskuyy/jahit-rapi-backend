<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    use HasFactory;

    protected $table = 'kasbon';

    protected $fillable = [
        'karyawan_id',
        'tanggal_pengajuan',
        'jumlah_total',
        'nominal_per_cicilan',
        'total_cicilan',
        'cicilan_terbayar',
        'status',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date:Y-m-d',
        'jumlah_total' => 'float',
        'nominal_per_cicilan' => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
