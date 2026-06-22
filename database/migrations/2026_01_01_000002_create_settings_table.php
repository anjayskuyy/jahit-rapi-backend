<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            // Tabel single-row (selalu id = 1). Lihat Setting::current().
            $table->id();
            $table->string('nama_usaha')->default('Jahit Rapi');
            $table->string('brand_inisial')->default('JR');
            // Disimpan sebagai string "HH:MM" (bukan kolom time SQL) supaya
            // identik dengan format yang dikirim/diterima frontend, tanpa
            // perlu casting tambahan.
            $table->string('jam_masuk_standar', 5)->default('08:00');
            $table->unsignedSmallInteger('toleransi_telat_menit')->default(15);
            $table->string('wa_gateway')->nullable();
            $table->string('wa_api_key')->nullable();
            $table->string('mesin_adms_host')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
