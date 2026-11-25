<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PermintaanResetSandi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Support\AturanPassword;

class LupaSandiController extends Controller
{
    public function form()
    {
        return view('auth.lupa');
    }

    public function kirim(Request $request)
    {
        $d = $request->validate([
            'email'    => 'required|email|max:150',
            'password' => ['required', 'confirmed', AturanPassword::rule()],
            'kunci'    => 'nullable|string|max:200',
        ]);

        // Pesan selalu sama, apa pun hasilnya, agar tidak bisa dipakai menebak email yang terdaftar.
        $pesan = 'Jika email itu terdaftar, permintaan Anda sudah dicatat. Untuk admin dan teknisi, password baru aktif setelah disetujui owner.';

        $email = strtolower(trim($d['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            AuditLog::catat('minta_reset_sandi', 'Email tidak terdaftar: ' . $email);
            return $this->selesai($pesan);
        }

        // ---- Owner: boleh langsung mengganti, tanpa persetujuan siapa pun, asal memegang kunci pemulihan ----
        if ($user->role === 'owner') {
            return $this->resetOwner($user, $d, $pesan);
        }

        // ---- Admin / teknisi: menunggu persetujuan owner ----
        PermintaanResetSandi::where('user_id', $user->id)->where('status', 'menunggu')->delete();   // permintaan terbaru menggantikan yang lama
        PermintaanResetSandi::create([
            'user_id'   => $user->id,
            'password'  => Hash::make($d['password']),   // Bcrypt; baru dipakai setelah disetujui
            'status'    => 'menunggu',
            'ip'        => $request->ip(),
            'perangkat' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);
        AuditLog::catat('minta_reset_sandi', 'Email: ' . $user->email, $user->id);

        return $this->selesai($pesan);
    }

    private function resetOwner(User $owner, array $d, string $pesan)
    {
        $kunciEnv = (string) config('bengkel.kunci_pemulihan_owner');
        $kunci = (string) ($d['kunci'] ?? '');

        if ($kunciEnv !== '' && $kunci !== '' && hash_equals($kunciEnv, $kunci)) {
            $owner->forceFill([
                'password'       => Hash::make($d['password']),
                'remember_token' => Str::random(60),
            ])->save();

            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $owner->id)->delete();
            }

            AuditLog::catat('reset_sandi_owner', 'Password owner diganti dengan kunci pemulihan', $owner->id);

            return redirect()->route('login')->with('ok', 'Password owner berhasil diganti. Silakan masuk dengan password baru.');
        }

        AuditLog::catat('reset_sandi_gagal', 'Percobaan reset password owner ' . $owner->email
            . ($kunciEnv === '' ? ' (kunci pemulihan belum diatur di .env)' : ''), $owner->id);

        return $this->selesai($pesan);
    }

    private function selesai(string $pesan)
    {
        return redirect()->route('login')->with('ok', $pesan);
    }
}
