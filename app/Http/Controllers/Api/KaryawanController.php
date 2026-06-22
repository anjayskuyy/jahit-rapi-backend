<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        return Karyawan::orderBy('nama')->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        return response()->json(Karyawan::create($data), 201);
    }

    public function show(Karyawan $karyawan)
    {
        return $karyawan;
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $this->validated($request);
        $karyawan->update($data);

        return $karyawan;
    }

    /**
     * Soft delete — riwayat absensi/lembur/kasbon/slip gaji yang sudah
     * tercatat tetap utuh, karyawan cuma hilang dari daftar aktif.
     */
    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return response()->json(['message' => 'Karyawan dihapus.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'bagian' => 'required|string|max:255',
            'jenis_gaji' => 'required|in:harian,bulanan',
            'rate_harian' => 'nullable|numeric|min:0',
            'gaji_bulanan' => 'nullable|numeric|min:0',
            'rate_lembur_per_jam' => 'required|numeric|min:0',
            'no_whatsapp' => 'required|string|max:30',
        ]);
    }
}
