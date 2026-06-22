<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'nama_usaha',
        'brand_inisial',
        'jam_masuk_standar',
        'toleransi_telat_menit',
        'wa_gateway',
        'wa_api_key',
        'mesin_adms_host',
    ];

    /**
     * Tabel settings selalu cuma punya 1 baris (id = 1).
     * Dipanggil di tempat lain sebagai Setting::current() biar nggak
     * perlu cek null di mana-mana — baris dibuat otomatis kalau belum ada.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'nama_usaha' => 'Jahit Rapi',
            'brand_inisial' => 'JR',
            'jam_masuk_standar' => '08:00',
            'toleransi_telat_menit' => 15,
        ]);
    }
}
