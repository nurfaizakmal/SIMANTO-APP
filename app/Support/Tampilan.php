<?php

namespace App\Support;

use Illuminate\Contracts\View\View;

/**
 * Memilih view sesuai peran pengguna yang sedang login.
 *
 * Tiap peran punya folder view sendiri (resources/views/owner, admin, teknisi),
 * sehingga isi halaman tiap peran terpisah dan tidak perlu percabangan @can / role di dalam view.
 * Contoh: Tampilan::buat('servis.index') -> owner.servis.index / admin.servis.index / teknisi.servis.index
 */
class Tampilan
{
    public const PERAN = ['owner', 'admin', 'teknisi'];

    public static function buat(string $nama, array $data = []): View
    {
        $peran = auth()->user()?->role;
        abort_unless(in_array($peran, self::PERAN, true), 403);

        $view = "{$peran}.{$nama}";
        // peran tidak punya halaman ini (mis. teknisi tidak punya form servis) -> tolak, jangan error 500
        abort_unless(view()->exists($view), 403, 'Halaman ini tidak tersedia untuk peran Anda.');

        return view($view, $data);
    }
}
