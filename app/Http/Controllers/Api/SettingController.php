<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/settings
     */
    public function show()
    {
        return Setting::current();
    }

    /**
     * PUT /api/settings
     * Frontend selalu mengirim seluruh object settings tiap kali
     * (lihat pengaturan.js — tiap section save mengirim object penuh
     * yang sudah di-merge dari cache lokal), jadi update sederhana
     * tanpa perlu partial-merge di backend.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'brand_inisial' => 'required|string|max:5',
            'jam_masuk_standar' => 'required|date_format:H:i',
            'toleransi_telat_menit' => 'required|integer|min:0|max:120',
            'wa_gateway' => 'nullable|string|max:255',
            'wa_api_key' => 'nullable|string|max:255',
            'mesin_adms_host' => 'nullable|string|max:255',
        ]);

        $setting = Setting::current();
        $setting->update($data);

        return $setting;
    }
}
