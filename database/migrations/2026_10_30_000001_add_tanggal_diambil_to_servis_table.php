<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servis', function (Blueprint $table) {
            $table->date('tanggal_diambil')->nullable()->after('tanggal_selesai')->index();
        });

        // Servis yang sudah berstatus "diambil" sebelum kolom ini ada: isi dari riwayat status,
        // atau (bila tidak ada) dari tanggal selesai / tanggal dibuat.
        foreach (DB::table('servis')->where('status', 'diambil')->get(['id', 'tanggal_selesai', 'created_at']) as $s) {
            $waktu = DB::table('riwayat_status')->where('servis_id', $s->id)->where('ke_status', 'diambil')
                ->orderByDesc('id')->value('created_at');
            $tanggal = $waktu ?: ($s->tanggal_selesai ?: $s->created_at);

            DB::table('servis')->where('id', $s->id)->update(['tanggal_diambil' => substr((string) $tanggal, 0, 10)]);
        }
    }

    public function down(): void
    {
        Schema::table('servis', function (Blueprint $table) {
            $table->dropColumn('tanggal_diambil');
        });
    }
};
