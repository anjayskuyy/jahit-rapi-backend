<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            // nullable + set null: karyawan boleh di-soft-delete tanpa
            // merusak riwayat slip gaji (nama/bagian/jenis_gaji di bawah
            // ini sudah jadi snapshot, jadi tampilan slip tetap utuh).
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->nullOnDelete();

            // snapshot data karyawan pada saat payroll dijalankan
            $table->string('nama');
            $table->string('bagian');
            $table->enum('jenis_gaji', ['harian', 'bulanan']);

            $table->unsignedInteger('hari_hadir')->default(0);
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('total_jam_lembur', 6, 2)->default(0);
            $table->decimal('upah_lembur', 12, 2)->default(0);
            $table->decimal('potongan_kasbon', 12, 2)->default(0);
            $table->foreignId('kasbon_id')->nullable()->constrained('kasbon')->nullOnDelete();
            $table->decimal('total_bersih', 12, 2)->default(0);
            $table->enum('status_slip', ['belum_terkirim', 'terkirim'])->default('belum_terkirim');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
