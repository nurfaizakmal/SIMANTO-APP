<?php

namespace Database\Seeders;

use App\Models\Servis;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Password disimpan sebagai hash Bcrypt. GANTI semua password ini sebelum dipakai sungguhan!
        $akun = [
            ['Owner Bengkel',   'owner@bengkel.test',   'Owner#12345',   'owner'],
            ['Admin Bengkel',   'admin@bengkel.test',   'Admin#12345',   'admin'],
            ['Teknisi Bengkel', 'teknisi@bengkel.test', 'Teknisi#12345', 'teknisi'],
        ];

        foreach ($akun as [$nama, $email, $pass, $role]) {
            // role sengaja tidak masuk $fillable, jadi diisi lewat forceFill
            $u = User::firstOrNew(['email' => $email]);
            $u->forceFill(['name' => $nama, 'password' => Hash::make($pass), 'role' => $role])->save();
        }

        if (Servis::count() === 0) {
            Servis::create([
                'nama_pelanggan' => 'Budi Santoso',
                'no_hp'          => '081234567890',
                'plat_nomor'     => 'H 1234 AB',
                'merk_motor'     => 'Honda Beat 2019',
                'keluhan'        => 'Mesin brebet, ganti oli',
                'status'         => 'dikerjakan',
                'tanggal_masuk'  => now(),
            ]);
        }
    }
}
