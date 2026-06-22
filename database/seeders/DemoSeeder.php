<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Kasbon;
use App\Models\Lembur;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(['id' => 1], [
            'nama_usaha' => 'Konveksi Jahit Rapi',
            'brand_inisial' => 'JR',
            'jam_masuk_standar' => '08:00',
            'toleransi_telat_menit' => 15,
            'wa_gateway' => '',
            'wa_api_key' => '',
            'mesin_adms_host' => '192.168.1.201',
        ]);

        $karyawanData = [
            ['nama' => 'Siti Aminah', 'jabatan' => 'Penjahit Senior', 'bagian' => 'Jahit', 'jenis_gaji' => 'harian', 'rate_harian' => 120000, 'gaji_bulanan' => null, 'rate_lembur_per_jam' => 15000, 'no_whatsapp' => '081234560001'],
            ['nama' => 'Budi Santoso', 'jabatan' => 'Penjahit', 'bagian' => 'Jahit', 'jenis_gaji' => 'harian', 'rate_harian' => 100000, 'gaji_bulanan' => null, 'rate_lembur_per_jam' => 12500, 'no_whatsapp' => '081234560002'],
            ['nama' => 'Dewi Lestari', 'jabatan' => 'Quality Control', 'bagian' => 'QC', 'jenis_gaji' => 'bulanan', 'rate_harian' => null, 'gaji_bulanan' => 3200000, 'rate_lembur_per_jam' => 18000, 'no_whatsapp' => '081234560003'],
            ['nama' => 'Agus Wijaya', 'jabatan' => 'Pemotong Kain', 'bagian' => 'Cutting', 'jenis_gaji' => 'harian', 'rate_harian' => 110000, 'gaji_bulanan' => null, 'rate_lembur_per_jam' => 14000, 'no_whatsapp' => '081234560004'],
            ['nama' => 'Rina Marlina', 'jabatan' => 'Admin & Gudang', 'bagian' => 'Admin', 'jenis_gaji' => 'bulanan', 'rate_harian' => null, 'gaji_bulanan' => 2800000, 'rate_lembur_per_jam' => 16000, 'no_whatsapp' => '081234560005'],
        ];

        $karyawan = collect($karyawanData)->map(fn ($d) => Karyawan::create($d));

        // Absensi 7 hari terakhir (kecuali hari ini, biar dashboard "hari ini"
        // masih kelihatan kosong & bisa dites manual lewat halaman Absensi).
        foreach (range(1, 7) as $i) {
            $tanggal = Carbon::today()->subDays($i);
            foreach ($karyawan as $k) {
                // beberapa hari sengaja dilewati biar ada variasi 'absen'
                if (($i + $k->id) % 5 === 0) {
                    continue;
                }
                $terlambat = ($i + $k->id) % 4 === 0;
                Absensi::create([
                    'karyawan_id' => $k->id,
                    'tanggal' => $tanggal->toDateString(),
                    'jam_masuk' => $terlambat ? '08:25' : '07:55',
                    'metode' => $i % 3 === 0 ? 'fingerprint' : 'manual',
                    'status' => $terlambat ? 'terlambat' : 'tepat_waktu',
                ]);
            }
        }

        // Lembur bulan berjalan
        $bulanIni = Carbon::today()->startOfMonth();
        Lembur::create(['karyawan_id' => $karyawan[0]->id, 'tanggal' => $bulanIni->copy()->addDays(3)->toDateString(), 'jam' => 2.5, 'keterangan' => 'Kejar target order seragam sekolah']);
        Lembur::create(['karyawan_id' => $karyawan[1]->id, 'tanggal' => $bulanIni->copy()->addDays(5)->toDateString(), 'jam' => 1.5, 'keterangan' => null]);
        Lembur::create(['karyawan_id' => $karyawan[3]->id, 'tanggal' => $bulanIni->copy()->addDays(6)->toDateString(), 'jam' => 3, 'keterangan' => 'Potong bahan untuk besok']);

        // Kasbon: 1 masih berjalan, 1 sudah lunas
        Kasbon::create([
            'karyawan_id' => $karyawan[0]->id,
            'tanggal_pengajuan' => $bulanIni->copy()->addDays(2)->toDateString(),
            'jumlah_total' => 600000,
            'nominal_per_cicilan' => 100000,
            'total_cicilan' => 6,
            'cicilan_terbayar' => 2,
            'status' => 'berjalan',
        ]);
        Kasbon::create([
            'karyawan_id' => $karyawan[2]->id,
            'tanggal_pengajuan' => Carbon::today()->subMonths(2)->toDateString(),
            'jumlah_total' => 300000,
            'nominal_per_cicilan' => 150000,
            'total_cicilan' => 2,
            'cicilan_terbayar' => 2,
            'status' => 'lunas',
        ]);
    }
}
