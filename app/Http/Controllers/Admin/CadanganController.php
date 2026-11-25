<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Cadangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CadanganController extends Controller
{
    public function index()
    {
        $terakhir = fn (string $aksi) => AuditLog::with('user')->where('aksi', $aksi)->latest('id')->first();

        return view('admin.cadangan', [
            'jumlahServis' => DB::table('servis')->count(),
            'jumlahFoto'   => DB::table('servis')->whereNotNull('foto')->count(),
            'terakhirBuat' => $terakhir('buat_cadangan'),
            'terakhirPulih' => $terakhir('pulihkan_data'),
        ]);
    }

    /** Admin & owner: unduh cadangan (POST agar tidak terpicu oleh prefetch/tautan). */
    public function unduh(Request $request)
    {
        @set_time_limit(300);

        // Cadangan lengkap (memuat akun & hash password) hanya untuk owner.
        $lengkap = $request->input('jenis') === 'lengkap';
        if ($lengkap) {
            Gate::authorize('backup-lengkap');
        }

        $tmp = storage_path('app/cadangan/unduh-' . Str::random(16) . '.zip');
        $jumlah = Cadangan::buat($tmp, $request->user(), $lengkap);

        AuditLog::catat('buat_cadangan', ($lengkap ? 'LENGKAP, ' : 'standar, ')
            . "{$jumlah['servis']} servis, {$jumlah['foto']} foto" . ($lengkap ? ", {$jumlah['pengguna']} akun" : ''));

        return response()->download($tmp, 'cadangan-bengkel-' . ($lengkap ? 'lengkap-' : '') . now()->format('Ymd-His') . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /** Owner saja: pulihkan dari berkas cadangan. */
    public function pulihkan(Request $request)
    {
        $request->validate([
            'berkas'    => 'required|file|mimes:zip|max:512000',
            'password'  => 'required|string',
            'konfirmasi' => 'accepted',
            'pulihkan_pengguna' => 'nullable|boolean',
            'ketik'     => 'required_if:pulihkan_pengguna,1|nullable|string',
        ], [
            'berkas.required' => 'Pilih berkas cadangan (.zip).',
            'berkas.mimes'    => 'Berkas harus berformat .zip.',
            'berkas.max'      => 'Berkas terlalu besar (maks 500 MB).',
            'berkas.uploaded' => 'Unggahan gagal. Ukuran berkas mungkin melebihi batas server (upload_max_filesize / post_max_size).',
            'konfirmasi.accepted' => 'Centang kotak persetujuan terlebih dahulu.',
            'ketik.required_if'   => 'Ketik PULIHKAN AKUN untuk memulihkan akun pengguna.',
        ]);

        $denganPengguna = $request->boolean('pulihkan_pengguna');
        if ($denganPengguna && trim((string) $request->input('ketik')) !== 'PULIHKAN AKUN') {
            return back()->withErrors(['ketik' => 'Ketik persis: PULIHKAN AKUN'])->withInput($request->except('password'));
        }

        // Aksi merusak: minta password owner sekali lagi.
        if (!Hash::check((string) $request->input('password'), $request->user()->password)) {
            AuditLog::catat('pulihkan_gagal', 'Password konfirmasi salah');
            return back()->withErrors(['password' => 'Password salah.']);
        }

        @set_time_limit(600);

        try {
            $hasil = Cadangan::pulihkan($request->file('berkas')->getRealPath(), $request->user(), $denganPengguna);
        } catch (\DomainException $e) {
            AuditLog::catat('pulihkan_gagal', $e->getMessage());
            return back()->withErrors(['berkas' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);
            AuditLog::catat('pulihkan_gagal', 'Kesalahan sistem');
            return back()->withErrors(['berkas' => 'Pemulihan gagal karena kesalahan sistem. Data lama tidak diubah. Lihat storage/logs/laravel.log.']);
        }

        return redirect()->route('admin.cadangan.index')->with('ok',
            "Data dipulihkan dari cadangan {$hasil['dibuat_pada']}: {$hasil['servis']} servis, {$hasil['foto']} foto"
            . ($hasil['audit'] ? ", {$hasil['audit']} arsip audit log" : '')
            . ($hasil['denganPengguna'] ? ", {$hasil['pengguna']} akun" : '') . '. '
            . "Kondisi sebelumnya tersimpan di storage/app/cadangan/{$hasil['pengaman']}.");
    }
}
