<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kasbon;
use Illuminate\Http\Request;

class KasbonController extends Controller
{
    /**
     * GET /api/kasbon?status=berjalan|lunas|semua
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'semua');

        $query = Kasbon::query();
        if (in_array($status, ['berjalan', 'lunas'], true)) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('tanggal_pengajuan')->get();
    }

    /**
     * POST /api/kasbon
     * Body: { karyawan_id, tanggal_pengajuan, nominal_per_cicilan, total_cicilan }
     * jumlah_total dihitung di sini (nominal_per_cicilan * total_cicilan) —
     * frontend sengaja tidak mengirim jumlah_total secara langsung.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal_pengajuan' => 'required|date',
            'nominal_per_cicilan' => 'required|numeric|min:1',
            'total_cicilan' => 'required|integer|min:1',
        ]);

        $data['jumlah_total'] = round($data['nominal_per_cicilan'] * $data['total_cicilan'], 2);
        $data['cicilan_terbayar'] = 0;
        $data['status'] = 'berjalan';

        return response()->json(Kasbon::create($data), 201);
    }

    /**
     * POST /api/kasbon/{id}/bayar
     * Tandai 1 cicilan terbayar. Otomatis jadi 'lunas' kalau sudah
     * mencapai total_cicilan. Dipanggil juga otomatis dari
     * PayrollController::run() saat payroll dijalankan.
     */
    public function bayar(Kasbon $kasbon)
    {
        if ($kasbon->status === 'lunas') {
            return response()->json(['message' => 'Kasbon ini sudah lunas.'], 422);
        }

        $kasbon->cicilan_terbayar += 1;
        if ($kasbon->cicilan_terbayar >= $kasbon->total_cicilan) {
            $kasbon->status = 'lunas';
        }
        $kasbon->save();

        return $kasbon;
    }

    public function destroy(Kasbon $kasbon)
    {
        $kasbon->delete();

        return response()->json(['message' => 'Kasbon dihapus.']);
    }
}
