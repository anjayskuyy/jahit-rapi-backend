<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('bagian');
            $table->enum('jenis_gaji', ['harian', 'bulanan']);
            $table->decimal('rate_harian', 12, 2)->nullable();
            $table->decimal('gaji_bulanan', 12, 2)->nullable();
            $table->decimal('rate_lembur_per_jam', 12, 2)->default(0);
            $table->string('no_whatsapp');
            $table->timestamps();
            $table->softDeletes();
            // soft delete dipakai karena absensi/lembur/kasbon/payroll_items
            // menyimpan karyawan_id sebagai referensi historis — karyawan yang
            // "dihapus" dari UI tidak boleh menghilangkan riwayat itu.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
