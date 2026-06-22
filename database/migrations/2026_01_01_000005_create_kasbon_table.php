<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasbon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal_pengajuan');
            // jumlah_total = nominal_per_cicilan * total_cicilan, dihitung
            // backend saat dibuat (frontend cuma kirim nominal_per_cicilan +
            // total_cicilan, lihat KasbonController::store).
            $table->decimal('jumlah_total', 12, 2);
            $table->decimal('nominal_per_cicilan', 12, 2);
            $table->unsignedInteger('total_cicilan');
            $table->unsignedInteger('cicilan_terbayar')->default(0);
            $table->enum('status', ['berjalan', 'lunas'])->default('berjalan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasbon');
    }
};
