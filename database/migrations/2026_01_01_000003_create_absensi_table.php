<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal');
            // String "HH:MM", sama seperti settings.jam_masuk_standar.
            $table->string('jam_masuk', 5);
            $table->enum('metode', ['manual', 'fingerprint'])->default('manual');
            $table->enum('status', ['tepat_waktu', 'terlambat']);
            $table->timestamps();

            // satu karyawan cuma boleh punya 1 catatan absensi per tanggal
            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
