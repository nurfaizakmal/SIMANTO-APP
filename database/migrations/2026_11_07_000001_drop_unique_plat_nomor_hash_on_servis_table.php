<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Motor langganan boleh datang lagi dengan plat yang sama setelah servis sebelumnya "Sudah diambil".
     * Larangan plat kembar kini dijaga aplikasi hanya untuk motor yang masih aktif di bengkel,
     * jadi indeks UNIK diganti menjadi indeks biasa (kolom plat_nomor_hash tetap terindeks untuk pencarian).
     */
    public function up(): void
    {
        try {
            Schema::table('servis', function (Blueprint $table) {
                $table->dropUnique('servis_plat_nomor_hash_unique');
            });
        } catch (\Throwable $e) {
            // indeks unik tidak ada (dulu dilewati karena data kembar), tidak masalah
        }

        // Pastikan tetap ada indeks biasa untuk pencarian.
        try {
            Schema::table('servis', function (Blueprint $table) {
                $table->index('plat_nomor_hash', 'servis_plat_nomor_hash_index');
            });
        } catch (\Throwable $e) {
            // sudah ada
        }
    }

    public function down(): void
    {
        try {
            Schema::table('servis', function (Blueprint $table) {
                $table->dropIndex('servis_plat_nomor_hash_index');
            });
        } catch (\Throwable $e) {
        }

        $kembar = DB::table('servis')->select('plat_nomor_hash')->groupBy('plat_nomor_hash')->havingRaw('count(*) > 1')->exists();
        if (!$kembar) {
            Schema::table('servis', function (Blueprint $table) {
                $table->unique('plat_nomor_hash', 'servis_plat_nomor_hash_unique');
            });
        }
    }
};
