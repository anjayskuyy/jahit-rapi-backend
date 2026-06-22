<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'karyawan';

    protected $fillable = [
        'nama',
        'jabatan',
        'bagian',
        'jenis_gaji',
        'rate_harian',
        'gaji_bulanan',
        'rate_lembur_per_jam',
        'no_whatsapp',
    ];

    protected $casts = [
        'rate_harian' => 'float',
        'gaji_bulanan' => 'float',
        'rate_lembur_per_jam' => 'float',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function lembur()
    {
        return $this->hasMany(Lembur::class);
    }

    public function kasbon()
    {
        return $this->hasMany(Kasbon::class);
    }
}
