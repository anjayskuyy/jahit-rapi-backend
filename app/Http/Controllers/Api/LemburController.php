<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    /**
     * GET /api/lembur?bulan=06&tahun=2026
     */
    public function index(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);

        return Lembur::whereMonth('tanggal', $request->query('bulan'))
            ->whereYear('tanggal', $request->query('tahun'))
            ->orderByDesc('tanggal')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        return response()->json(Lembur::create($data), 201);
    }

    public function update(Request $request, Lembur $lembur)
    {
        $data = $this->validated($request);
        $lembur->update($data);

        return $lembur;
    }

    public function destroy(Lembur $lembur)
    {
        $lembur->delete();

        return response()->json(['message' => 'Lembur dihapus.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal' => 'required|date',
            'jam' => 'required|numeric|min:0.5|max:24',
            'keterangan' => 'nullable|string|max:255',
        ]);
    }
}
