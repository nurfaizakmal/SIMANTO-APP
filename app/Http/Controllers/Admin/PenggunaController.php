<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Support\AturanPassword;

class PenggunaController extends Controller
{
    private const ROLE = ['teknisi', 'admin', 'owner'];

    public function index()
    {
        return view('admin.pengguna.index', ['daftar' => User::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.pengguna.form', ['pengguna' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'role'     => ['required', Rule::in(self::ROLE)],
            'password' => ['required', 'confirmed', AturanPassword::rule()],
        ]);

        // role tidak ada di $fillable, jadi diisi lewat forceFill; password disimpan sebagai hash Bcrypt
        (new User())->forceFill([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ])->save();

        AuditLog::catat('buat_pengguna', "{$data['email']} sebagai {$data['role']}");

        return redirect()->route('admin.pengguna.index')->with('ok', 'Pengguna ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        return view('admin.pengguna.form', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna)
    {
        $diriSendiri = $pengguna->is($request->user());
        $roleLama = $pengguna->role;

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($pengguna->id)],
            // role akun sendiri dikunci agar owner tidak bisa menurunkan dirinya sendiri
            'role'     => [$diriSendiri ? 'nullable' : 'required', Rule::in(self::ROLE)],
            'password' => ['nullable', 'confirmed', AturanPassword::rule()],
        ]);

        $pengguna->forceFill(['name' => $data['name'], 'email' => $data['email']]);

        $ket = [$data['email']];
        if (!$diriSendiri) {
            $pengguna->role = $data['role'];
            if ($roleLama !== $data['role']) {
                $ket[] = "role {$roleLama} → {$data['role']}";
            }
        }
        if (!empty($data['password'])) {
            $pengguna->password = Hash::make($data['password']);
            $ket[] = 'password diganti';
        }
        $pengguna->save();

        AuditLog::catat('ubah_pengguna', implode(', ', $ket));

        return redirect()->route('admin.pengguna.index')->with('ok', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, User $pengguna)
    {
        if ($pengguna->is($request->user())) {
            return back()->withErrors(['hapus' => 'Anda tidak bisa menghapus akun yang sedang dipakai.']);
        }

        AuditLog::catat('hapus_pengguna', $pengguna->email);
        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')->with('ok', 'Pengguna dihapus.');
    }
}
