<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\KasbonController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Jahit Rapi
|--------------------------------------------------------------------------
| Cocokkan 1:1 dengan path & method yang dipanggil dari
| assets/js/api.js di project frontend. Kalau ada path yang diubah
| di salah satu sisi, ubah juga di sisi yang lain.
*/

// ---------- AUTH (publik) ----------
Route::post('/login', [AuthController::class, 'login']);

// ---------- SEMUA ROUTE DI BAWAH INI WAJIB LOGIN (Bearer token) ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Karyawan
    Route::get('/karyawan', [KaryawanController::class, 'index']);
    Route::post('/karyawan', [KaryawanController::class, 'store']);
    Route::get('/karyawan/{karyawan}', [KaryawanController::class, 'show']);
    Route::put('/karyawan/{karyawan}', [KaryawanController::class, 'update']);
    Route::delete('/karyawan/{karyawan}', [KaryawanController::class, 'destroy']);

    // Absensi
    Route::get('/absensi', [AbsensiController::class, 'index']);
    Route::post('/absensi', [AbsensiController::class, 'store']);
    Route::post('/absensi/absen-semua', [AbsensiController::class, 'absenSemua']);
    Route::delete('/absensi/{absensi}', [AbsensiController::class, 'destroy']);

    // Lembur
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::put('/lembur/{lembur}', [LemburController::class, 'update']);
    Route::delete('/lembur/{lembur}', [LemburController::class, 'destroy']);

    // Kasbon
    Route::get('/kasbon', [KasbonController::class, 'index']);
    Route::post('/kasbon', [KasbonController::class, 'store']);
    Route::post('/kasbon/{kasbon}/bayar', [KasbonController::class, 'bayar']);
    Route::delete('/kasbon/{kasbon}', [KasbonController::class, 'destroy']);

    // Payroll
    // NB: rute statis /payroll/preview dan /payroll/run WAJIB didaftarkan
    // sebelum /payroll/{payroll}, kalau tidak "preview"/"run" akan
    // ketangkep sebagai {payroll} route-model-binding dan 404/500.
    Route::get('/payroll/preview', [PayrollController::class, 'preview']);
    Route::post('/payroll/run', [PayrollController::class, 'run']);
    Route::get('/payroll', [PayrollController::class, 'index']);
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show']);
    Route::patch('/payroll/items/{item}/kirim', [PayrollController::class, 'kirim']);

    // Laporan
    Route::get('/laporan/kehadiran', [LaporanController::class, 'kehadiran']);
    Route::get('/laporan/lembur', [LaporanController::class, 'lembur']);
    Route::get('/laporan/payroll', [LaporanController::class, 'payroll']);

    // Settings
    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);
});
