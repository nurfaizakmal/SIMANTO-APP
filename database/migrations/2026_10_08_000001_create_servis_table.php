<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_servis', 20)->unique();      // kode pelacakan untuk pelanggan
            $table->string('nama_pelanggan');
            $table->string('no_hp', 20)->nullable();
            $table->text('plat_nomor');                        // terenkripsi AES-256 (ciphertext panjang -> text)
            $table->char('plat_nomor_hash', 64)->index();      // blind index (HMAC-SHA256) untuk pencarian
            $table->string('merk_motor');
            $table->text('keluhan');
            $table->enum('status', ['menunggu', 'dikerjakan', 'menunggu_sparepart', 'selesai', 'diambil'])
                  ->default('menunggu');
            $table->text('catatan')->nullable();
            $table->date('tanggal_masuk');
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servis');
    }
};
