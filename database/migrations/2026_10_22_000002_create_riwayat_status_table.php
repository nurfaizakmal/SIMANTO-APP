<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servis_id')->constrained('servis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dari_status', 30)->nullable();
            $table->string('ke_status', 30);
            $table->timestamps();
        });

        // Isi riwayat awal untuk servis yang sudah ada sebelum fitur ini.
        $sekarang = now();
        foreach (DB::table('servis')->get(['id', 'status', 'created_at']) as $s) {
            DB::table('riwayat_status')->insert([
                'servis_id'   => $s->id,
                'user_id'     => null,
                'dari_status' => null,
                'ke_status'   => $s->status,
                'created_at'  => $s->created_at ?? $sekarang,
                'updated_at'  => $s->created_at ?? $sekarang,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};
