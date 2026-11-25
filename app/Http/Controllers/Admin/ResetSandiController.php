<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PermintaanResetSandi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResetSandiController extends Controller
{
    public function index()
    {
        return view('admin.reset-sandi', [
            'menunggu' => PermintaanResetSandi::menunggu()->with('user')->latest('id')->get(),
            'riwayat'  => PermintaanResetSandi::where('status', '!=', 'menunggu')
                ->with('user', 'pemroses')->latest('id')->limit(10)->get(),
        ]);
    }

    public function setujui(Request $request, PermintaanResetSandi $permintaan)
    {
        if (!$permintaan->masihBerlaku() || !$permintaan->user) {
            return back()->withErrors(['proses' => 'Permintaan ini sudah diproses atau kedaluwarsa.']);
        }

        // Hash Bcrypt yang diminta pengguna disalin apa adanya (tidak di-hash dua kali).
        DB::table('users')->where('id', $permintaan->user_id)->update([
            'password'       => $permintaan->password,
            'remember_token' => Str::random(60),
            'updated_at'     => now(),
        ]);

        // Keluarkan sesi lama akun itu agar password lama/pencuri sesi tidak bertahan.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $permintaan->user_id)->delete();
        }

        $permintaan->update(['status' => 'disetujui', 'diproses_oleh' => $request->user()->id, 'diproses_at' => now()]);
        AuditLog::catat('setujui_reset_sandi', 'Akun: ' . $permintaan->user->email);

        return back()->with('ok', 'Password baru untuk ' . $permintaan->user->name . ' sudah aktif.');
    }

    public function tolak(Request $request, PermintaanResetSandi $permintaan)
    {
        if ($permintaan->status !== 'menunggu') {
            return back();
        }

        $permintaan->update(['status' => 'ditolak', 'diproses_oleh' => $request->user()->id, 'diproses_at' => now()]);
        AuditLog::catat('tolak_reset_sandi', 'Akun: ' . ($permintaan->user?->email ?? '-'));

        return back()->with('ok', 'Permintaan reset password ditolak.');
    }
}
