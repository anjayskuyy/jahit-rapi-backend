<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    use HasFactory;

    protected $table = 'payroll_runs';

    protected $fillable = [
        'tanggal_mulai',
        'tanggal_selesai',
        'grand_total',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date:Y-m-d',
        'tanggal_selesai' => 'date:Y-m-d',
        'grand_total' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }
}
