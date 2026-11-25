<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu plat nomor hanya boleh dipakai satu motor. Aturan ini sudah dijaga oleh validasi aplikasi;
     * migration ini menambah pengaman di tingkat database. Jika data lama sudah punya plat kembar,
     * indeks unik dilewati (hapus/ubah data kembar itu lalu jalankan ulang migration ini).
     */
    public function up(): void
    {
        $kembar = DB::table('servis')->select('plat_nomor_hash')
            ->groupBy('plat_nomor_hash')->havingRaw('count(*) > 1')->exists();

        if ($kembar) {
            echo "  PERINGATAN: ada plat nomor kembar di data lama, indeks unik dilewati.\n";
            return;
        }

        Schema::table('servis', function (Blueprint $table) {
            $table->unique('plat_nomor_hash', 'servis_plat_nomor_hash_unique');
        });
    }

    public function down(): void
    {
        try {
            Schema::table('servis', function (Blueprint $table) {
                $table->dropUnique('servis_plat_nomor_hash_unique');
            });
        } catch (\Throwable $e) {
            // indeks tidak ada (dilewati saat up), tidak masalah
        }
    }
};
