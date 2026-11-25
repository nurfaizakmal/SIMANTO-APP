<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // default 'teknisi' = hak paling kecil (hanya baca)
            $table->string('role', 20)->default('teknisi')->after('password');
        });

        // Akun pertama yang sudah ada (sebelumnya admin penuh) dijadikan owner,
        // supaya Anda tidak kehilangan akses setelah migrasi.
        $idPertama = DB::table('users')->min('id');
        if ($idPertama) {
            DB::table('users')->where('id', $idPertama)->update(['role' => 'owner']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
