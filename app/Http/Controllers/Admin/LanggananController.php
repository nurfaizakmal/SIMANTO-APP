<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servis;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LanggananController extends Controller
{
    /**
     * Daftar motor langganan: plat yang sama sudah masuk LEBIH DARI 3 kali
     * (batas ada di config/bengkel.php -> langganan_minimal_kunjungan).
     * Plat tersimpan terenkripsi, jadi pengelompokan memakai kolom plat_nomor_hash.
     */
    public function index(Request $request)
    {
        $batas = Servis::batasLangganan();

        $baris = Servis::daftarLangganan();

        // Pencarian (nama / plat lengkap) dilakukan di PHP karena plat terenkripsi
        if ($cari = trim((string) $request->query('cari'))) {
            $hash = Servis::hashPlat($cari);
            $baris = $baris->filter(fn ($b) => $b->servis->plat_nomor_hash === $hash
                || mb_stripos($b->servis->nama_pelanggan, $cari) !== false);
        }

        $total = $baris->count();
        $perHalaman = 15;
        $halaman = LengthAwarePaginator::resolveCurrentPage();
        $daftar = new LengthAwarePaginator(
            $baris->forPage($halaman, $perHalaman)->values(),
            $total, $perHalaman, $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.langganan.index', compact('daftar', 'batas', 'total'));
    }

    /** Riwayat lengkap satu motor langganan: kerusakan (keluhan) dan perbaikan (catatan mekanik) tiap kunjungan. */
    public function show(Servis $servis)
    {
        $kunjungan = Servis::where('plat_nomor_hash', $servis->plat_nomor_hash)
            ->orderByDesc('tanggal_masuk')->orderByDesc('id')->get();

        $batas = Servis::batasLangganan();
        $langganan = $kunjungan->count() >= $batas;
        $terbaru = $kunjungan->first();

        $totalBiaya = (int) $kunjungan->where('status', 'diambil')->sum('biaya');

        return view('admin.langganan.show', compact('kunjungan', 'langganan', 'batas', 'terbaru', 'totalBiaya'));
    }
}
