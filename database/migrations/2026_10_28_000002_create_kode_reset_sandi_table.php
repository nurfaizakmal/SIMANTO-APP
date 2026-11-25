<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_reset_sandi', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->index();
            $table->string('kode');                                  // hash Bcrypt dari kode 6 angka
            $table->unsignedTinyInteger('percobaan')->default(0);    // jumlah tebakan salah
            $table->timestamp('kadaluarsa_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_reset_sandi');
    }
};
