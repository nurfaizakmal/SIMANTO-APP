<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Reset password lewat kode email diganti dengan persetujuan owner, tabel kodenya tidak dipakai lagi.
    public function up(): void
    {
        Schema::dropIfExists('kode_reset_sandi');
    }

    public function down(): void
    {
        //
    }
};
