<?php

namespace App\Http\Controllers;

use App\Models\Servis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    private function cari(string $kode): ?Servis
    {
        return Servis::with('riwayat')->where('kode_servis', strtoupper(trim($kode)))->first();
    }

    /** Angka ringkas untuk halaman depan (hanya jumlah, tanpa data pelanggan). */
    private function statistik(): array
    {
        $per = Servis::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status');

        return [
            'di_bengkel'    => (int) (($per['menunggu'] ?? 0) + ($per['dikerjakan'] ?? 0) + ($per['menunggu_sparepart'] ?? 0)),
            'siap_ambil'    => (int) ($per['selesai'] ?? 0),
            'selesai_bulan' => Servis::whereIn('status', ['selesai', 'diambil'])
                ->where('tanggal_selesai', '>=', now()->startOfMonth())->count(),
        ];
    }

    /** Motor yang sedang ada di bengkel dan sudah difoto (untuk galeri di halaman depan). */
    private function galeri()
    {
        return Servis::whereNotNull('foto')->where('status', '!=', 'diambil')
            ->latest('id')->limit(8)->get();
    }

    public function index()
    {
        return view('cek', ['stat' => $this->statistik(), 'galeri' => $this->galeri()]);
    }

    // Dari form di halaman utama
    public function cek(Request $request)
    {
        $data = $request->validate([
            'kode_servis' => 'required|string|max:20',
        ]);

        $servis = $this->cari($data['kode_servis']);

        if (!$servis) {
            return back()->withInput()
                ->withErrors(['kode_servis' => 'Kode servis tidak ditemukan. Periksa kembali kode yang diberikan bengkel.']);
        }

        return view('cek', ['servis' => $servis, 'stat' => $this->statistik(), 'galeri' => $this->galeri()]);
    }

    // Tautan langsung: /status/SRV-XXXXXX
    public function tampil(string $kode)
    {
        $servis = $this->cari($kode);

        if (!$servis) {
            return redirect()->route('home')
                ->withErrors(['kode_servis' => 'Kode servis tidak ditemukan. Periksa kembali kode yang diberikan bengkel.']);
        }

        return view('cek', ['servis' => $servis, 'stat' => $this->statistik(), 'galeri' => $this->galeri()]);
    }
}
