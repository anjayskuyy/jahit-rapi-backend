<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $table = 'payroll_items';

    protected $fillable = [
        'payroll_run_id',
        'karyawan_id',
        'nama',
        'bagian',
        'no_whatsapp', // FIX: tambah no_whatsapp
        'jenis_gaji',
        'hari_hadir',
        'gaji_pokok',
        'total_jam_lembur',
        'upah_lembur',
        'potongan_kasbon',
        'kasbon_id',
        'total_bersih',
        'status_slip',
    ];

    protected $casts = [
        'gaji_pokok'       => 'float',
        'total_jam_lembur' => 'float',
        'upah_lembur'      => 'float',
        'potongan_kasbon'  => 'float',
        'total_bersih'     => 'float',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class)->withTrashed();
    }

    public function kasbon()
    {
        return $this->belongsTo(Kasbon::class);
    }
}
