<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('jam', 5, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembur');
    }
};
