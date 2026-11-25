<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\AturanPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Auth::attempt membandingkan password dengan hash Bcrypt di database
        if (Auth::attempt($cred, $request->boolean('remember'))) {
            $request->session()->regenerate();
            AuditLog::catat('login', 'Berhasil masuk', Auth::id());

            // Password lama yang dibuat sebelum aturan baru tidak ditolak (agar akun tidak terkunci),
            // tetapi pemiliknya diberi tahu supaya segera menggantinya.
            if (!AturanPassword::memenuhi((string) $cred['password'])) {
                session()->flash('peringatan', 'Password Anda belum memenuhi aturan baru (minimal 8 karakter, memakai huruf, angka, dan simbol khusus). '
                    . (Auth::user()->role === 'owner'
                        ? 'Ubah lewat menu Pengguna, pilih akun Anda, lalu Ubah.'
                        : 'Minta owner mengubahnya lewat menu Pengguna.'));
            }

            // semua role masuk ke ringkasan masing-masing
            $tujuan = route('admin.dashboard');

            return redirect()->intended($tujuan);
        }

        // Hanya email yang dicatat, password tidak pernah disimpan di log
        AuditLog::catat('login_gagal', 'Email: ' . $cred['email']);

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        AuditLog::catat('logout', null, Auth::id());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
