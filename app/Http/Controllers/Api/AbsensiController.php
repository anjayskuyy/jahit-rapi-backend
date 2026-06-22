<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * GET /api/absensi?tanggal=2026-06-21
     */
    public function index(Request $request)
    {
        $request->validate(['tanggal' => 'required|date']);

        return Absensi::where('tanggal', $request->query('tanggal'))->get();
    }

    /**
     * POST /api/absensi
     * Body: { karyawan_id, tanggal, jam_masuk: "HH:MM", metode }
     * status (tepat_waktu/terlambat) dihitung di sini, bukan dikirim
     * frontend. updateOrCreate supaya "catat ulang" pada tanggal yang
     * sama menimpa catatan lama, bukan duplikat.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'required|date_format:H:i',
            'metode' => 'required|in:manual,fingerprint',
        ]);

        $status = $this->hitungStatus($data['jam_masuk']);

        $absensi = Absensi::updateOrCreate(
            ['karyawan_id' => $data['karyawan_id'], 'tanggal' => $data['tanggal']],
            ['jam_masuk' => $data['jam_masuk'], 'metode' => $data['metode'], 'status' => $status]
        );

        return response()->json($absensi, 201);
    }

    /**
     * POST /api/absensi/absen-semua
     * Body: { tanggal }
     * Tandai semua karyawan yang BELUM punya catatan absensi pada
     * tanggal ini sebagai hadir tepat waktu (jam masuk = jam standar).
     */
    public function absenSemua(Request $request)
    {
        $data = $request->validate(['tanggal' => 'required|date']);
        $tanggal = $data['tanggal'];
        $setting = Setting::current();

        $sudahAbsenIds = Absensi::where('tanggal', $tanggal)->pluck('karyawan_id');
        $belumAbsen = Karyawan::whereNotIn('id', $sudahAbsenIds)->get();

        foreach ($belumAbsen as $k) {
            Absensi::create([
                'karyawan_id' => $k->id,
                'tanggal' => $tanggal,
                'jam_masuk' => $setting->jam_masuk_standar,
                'metode' => 'manual',
                'status' => 'tepat_waktu',
            ]);
        }

        return Absensi::where('tanggal', $tanggal)->get();
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();

        return response()->json(['message' => 'Absensi dihapus.']);
    }

    /**
     * Bandingkan jam masuk terhadap jam_masuk_standar + toleransi_telat_menit
     * dari settings. Keduanya dianggap waktu pada hari yang sama supaya
     * perbandingan jam:menit murni, tanpa terganggu tanggal.
     */
    private function hitungStatus(string $jamMasuk): string
    {
        $setting = Setting::current();

        [$hStd, $mStd] = array_map('intval', explode(':', $setting->jam_masuk_standar));
        $batas = Carbon::createFromTime($hStd, $mStd, 0)
            ->addMinutes($setting->toleransi_telat_menit);

        [$h, $m] = array_map('intval', explode(':', $jamMasuk));
        $masuk = Carbon::createFromTime($h, $m, 0);

        return $masuk->lte($batas) ? 'tepat_waktu' : 'terlambat';
    }
}
