<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Kasbon;
use App\Models\Lembur;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * GET /api/payroll/preview?tanggal_mulai=...&tanggal_selesai=...
     * Hitung tanpa menyimpan. Response: { grand_total, items: [...] }
     * (items belum punya id/status_slip — belum tersimpan).
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $hasil = $this->hitungPeriode(
            $request->query('tanggal_mulai'),
            $request->query('tanggal_selesai')
        );

        return response()->json($hasil);
    }

    /**
     * POST /api/payroll/run
     * Body: { tanggal_mulai, tanggal_selesai }
     * Hitung ulang (sengaja tidak menerima hasil preview dari frontend,
     * supaya nilai final selalu dihitung dari data terbaru di server)
     * lalu simpan sebagai PayrollRun + PayrollItem. Kasbon aktif yang
     * kena potongan otomatis ditandai 1 cicilan terbayar.
     */
    public function run(Request $request)
    {
        $data = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $hasil = $this->hitungPeriode($data['tanggal_mulai'], $data['tanggal_selesai']);

        $run = DB::transaction(function () use ($data, $hasil) {
            $run = PayrollRun::create([
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'grand_total' => $hasil['grand_total'],
            ]);

            foreach ($hasil['items'] as $item) {
                $run->items()->create([
                    'karyawan_id' => $item['karyawan_id'],
                    'nama' => $item['nama'],
                    'bagian' => $item['bagian'],
                    'jenis_gaji' => $item['jenis_gaji'],
                    'hari_hadir' => $item['hari_hadir'],
                    'gaji_pokok' => $item['gaji_pokok'],
                    'total_jam_lembur' => $item['total_jam_lembur'],
                    'upah_lembur' => $item['upah_lembur'],
                    'potongan_kasbon' => $item['potongan_kasbon'],
                    'kasbon_id' => $item['kasbon_id'],
                    'total_bersih' => $item['total_bersih'],
                    'status_slip' => 'belum_terkirim',
                ]);

                if ($item['kasbon_id']) {
                    $kasbon = Kasbon::find($item['kasbon_id']);
                    if ($kasbon && $kasbon->status === 'berjalan') {
                        $kasbon->cicilan_terbayar += 1;
                        if ($kasbon->cicilan_terbayar >= $kasbon->total_cicilan) {
                            $kasbon->status = 'lunas';
                        }
                        $kasbon->save();
                    }
                }
            }

            return $run;
        });

        return response()->json($run->load('items'), 201);
    }

    /**
     * GET /api/payroll — daftar ringkas semua run (tanpa items).
     */
    public function index()
    {
        return PayrollRun::withCount('items as total_karyawan')
            ->withCount(['items as slip_terkirim' => function ($q) {
                $q->where('status_slip', 'terkirim');
            }])
            ->orderByDesc('tanggal_mulai')
            ->get();
    }

    /**
     * GET /api/payroll/{id} — detail run + semua item-nya.
     */
    public function show(PayrollRun $payroll)
    {
        return $payroll->load('items');
    }

    /**
     * PATCH /api/payroll/items/{itemId}/kirim
     */
    public function kirim(PayrollItem $item)
    {
        $item->status_slip = 'terkirim';
        $item->save();

        return $item;
    }

    /**
     * Logika inti penghitungan payroll, dipakai bareng oleh preview() & run().
     *
     * Per karyawan:
     * - hari_hadir: jumlah absensi (tepat_waktu/terlambat) dalam periode
     * - gaji_pokok: rate_harian * hari_hadir (kalau harian) atau
     *   gaji_bulanan penuh (kalau bulanan — tidak diprorata)
     * - upah_lembur: total jam lembur dalam periode * rate_lembur_per_jam
     * - potongan_kasbon: 1x nominal_per_cicilan dari kasbon aktif
     *   (kasbon status='berjalan') paling lama, kalau ada
     * - karyawan harian tanpa kehadiran & tanpa lembur di periode ini
     *   dilewati (tidak masuk daftar — tidak ada yang perlu dibayar)
     */
    private function hitungPeriode(string $mulai, string $selesai): array
    {
        $karyawanList = Karyawan::orderBy('nama')->get();
        $items = [];
        $grandTotal = 0;

        foreach ($karyawanList as $k) {
            $hariHadir = Absensi::where('karyawan_id', $k->id)
                ->whereBetween('tanggal', [$mulai, $selesai])
                ->whereIn('status', ['tepat_waktu', 'terlambat'])
                ->count();

            $gajiPokok = $k->jenis_gaji === 'harian'
                ? (float) $k->rate_harian * $hariHadir
                : (float) $k->gaji_bulanan;

            $totalJamLembur = (float) Lembur::where('karyawan_id', $k->id)
                ->whereBetween('tanggal', [$mulai, $selesai])
                ->sum('jam');

            $upahLembur = $totalJamLembur * (float) $k->rate_lembur_per_jam;

            $kasbonAktif = Kasbon::where('karyawan_id', $k->id)
                ->where('status', 'berjalan')
                ->orderBy('tanggal_pengajuan')
                ->first();

            $potonganKasbon = $kasbonAktif ? (float) $kasbonAktif->nominal_per_cicilan : 0;
            $kasbonId = $kasbonAktif?->id;

            if ($k->jenis_gaji === 'harian' && $hariHadir === 0 && $totalJamLembur == 0.0) {
                continue;
            }

            $totalBersih = $gajiPokok + $upahLembur - $potonganKasbon;

            $items[] = [
                'karyawan_id' => $k->id,
                'nama' => $k->nama,
                'bagian' => $k->bagian,
                'jenis_gaji' => $k->jenis_gaji,
                'hari_hadir' => $hariHadir,
                'gaji_pokok' => round($gajiPokok, 2),
                'total_jam_lembur' => $totalJamLembur,
                'upah_lembur' => round($upahLembur, 2),
                'potongan_kasbon' => round($potonganKasbon, 2),
                'kasbon_id' => $kasbonId,
                'total_bersih' => round($totalBersih, 2),
            ];

            $grandTotal += $totalBersih;
        }

        return [
            'grand_total' => round($grandTotal, 2),
            'items' => $items,
        ];
    }
}
