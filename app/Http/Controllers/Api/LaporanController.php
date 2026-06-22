<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * GET /api/laporan/kehadiran?bulan=06&tahun=2026
     * Response: { dates: [...], rekap: [{ karyawan_id, nama, bagian,
     *   hadir, terlambat, absen, kalender: [{tanggal, status}] }] }
     * status kalender per hari: 'tepat_waktu' | 'terlambat' | 'absen' |
     *   null (hari yang belum lewat — frontend menampilkannya netral).
     */
    public function kehadiran(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);
        $bulan = (int) $request->query('bulan');
        $tahun = (int) $request->query('tahun');

        $awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();
        $batasAkhir = $akhir->isFuture() ? Carbon::today() : $akhir;

        $dates = [];
        for ($d = $awal->copy(); $d->lte($akhir); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        $karyawanList = Karyawan::orderBy('nama')->get();
        $absensiBulan = Absensi::whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->get()
            ->groupBy('karyawan_id');

        $rekap = $karyawanList->map(function ($k) use ($dates, $absensiBulan, $batasAkhir) {
            $entriesByDate = ($absensiBulan->get($k->id) ?? collect())
                ->keyBy(fn ($a) => $a->tanggal->toDateString());

            $hadir = 0;
            $terlambat = 0;
            $absen = 0;
            $kalender = [];

            foreach ($dates as $tgl) {
                $entry = $entriesByDate->get($tgl);

                if ($entry) {
                    $status = $entry->status; // tepat_waktu | terlambat
                    $hadir++;
                    if ($status === 'terlambat') {
                        $terlambat++;
                    }
                } elseif (Carbon::parse($tgl)->lte($batasAkhir)) {
                    $status = 'absen';
                    $absen++;
                } else {
                    $status = null; // belum lewat, jangan dihitung absen
                }

                $kalender[] = ['tanggal' => $tgl, 'status' => $status];
            }

            return [
                'karyawan_id' => $k->id,
                'nama' => $k->nama,
                'bagian' => $k->bagian,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'absen' => $absen,
                'kalender' => $kalender,
            ];
        })->values();

        return response()->json(['dates' => $dates, 'rekap' => $rekap]);
    }

    /**
     * GET /api/laporan/lembur?bulan=06&tahun=2026
     * Response: { rekap: [{ karyawan_id, nama, bagian, sesi, jam,
     *   rate_per_jam, upah }] } — hanya karyawan dengan >=1 sesi lembur.
     */
    public function lembur(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);
        $bulan = (int) $request->query('bulan');
        $tahun = (int) $request->query('tahun');

        $karyawanList = Karyawan::orderBy('nama')->get();
        $lemburBulan = Lembur::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->groupBy('karyawan_id');

        $rekap = $karyawanList->map(function ($k) use ($lemburBulan) {
            $entries = $lemburBulan->get($k->id) ?? collect();
            $totalJam = (float) $entries->sum('jam');

            return [
                'karyawan_id' => $k->id,
                'nama' => $k->nama,
                'bagian' => $k->bagian,
                'sesi' => $entries->count(),
                'jam' => $totalJam,
                'rate_per_jam' => (float) $k->rate_lembur_per_jam,
                'upah' => round($totalJam * (float) $k->rate_lembur_per_jam, 2),
            ];
        })->filter(fn ($r) => $r['sesi'] > 0)->values();

        return response()->json(['rekap' => $rekap]);
    }

    /**
     * GET /api/laporan/payroll?bulan=06&tahun=2026
     * Response: { runs: [...] } — semua payroll run yang tanggal_mulai-nya
     * jatuh di bulan/tahun ini, lengkap dengan items (sama bentuknya
     * dengan payload GET /api/payroll/{id}).
     */
    public function payroll(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);

        $runs = PayrollRun::with('items')
            ->whereYear('tanggal_mulai', (int) $request->query('tahun'))
            ->whereMonth('tanggal_mulai', (int) $request->query('bulan'))
            ->orderByDesc('tanggal_mulai')
            ->get();

        return response()->json(['runs' => $runs]);
    }
}
