<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Servis;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---- jumlah per status (dipakai semua role) ----
        $status = Servis::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $total = (int) $status->sum();

        $data = [
            'status'          => $status,
            'total'           => $total,
            'menunggu'        => (int) ($status['menunggu'] ?? 0),
            'berjalan'        => (int) (($status['menunggu'] ?? 0) + ($status['dikerjakan'] ?? 0) + ($status['menunggu_sparepart'] ?? 0)),
            'siapAmbil'       => (int) ($status['selesai'] ?? 0),
            'selesaiBulanIni' => Servis::whereIn('status', ['selesai', 'diambil'])
                ->where('tanggal_selesai', '>=', now()->startOfMonth())->count(),
            'warna'           => [
                'menunggu' => '#6b7a87', 'dikerjakan' => '#1f6fb2', 'menunggu_sparepart' => '#c97a0a',
                'selesai'  => '#2e9e6b', 'diambil'    => '#4a5a68',
            ],
            'terbaru'         => Servis::where('status', '!=', 'diambil')->latest('id')->limit(6)->get(),
        ];

        // Bagian tambahan khusus owner: grafik bulanan, lama pengerjaan, aktivitas
        if ($request->user()->role === 'owner') {
            $data += $this->dataOwner();
        }

        // Keuangan: owner melihat semuanya (kartu + grafik); admin hanya pendapatan hari ini;
        // teknisi tidak melihat nominal biaya sama sekali.
        $peran = $request->user()->role;
        if ($peran === 'owner') {
            $data += $this->dataUang();
        } elseif ($peran === 'admin') {
            $data += $this->dataUangAdmin();
        }

        return view('admin.dashboard', $data);
    }

    /** Versi ringkas untuk admin: hanya pendapatan hari ini dan jumlah servis diambil yang biayanya belum diisi. */
    private function dataUangAdmin(): array
    {
        $diambil = fn () => Servis::where('status', 'diambil');

        return ['uang' => [
            'hari'       => (int) $diambil()->where('tanggal_diambil', today()->toDateString())->sum('biaya'),
            'tanpaBiaya' => $diambil()->whereNull('biaya')->count(),
        ]];
    }

    /** Pendapatan = total biaya servis yang sudah DIAMBIL pelanggan, dihitung per tanggal diambil. */
    private function dataUang(): array
    {
        $diambil = fn () => Servis::where('status', 'diambil');
        $jumlah = fn ($dari, $sampai) => (int) $diambil()
            ->whereBetween('tanggal_diambil', [$dari->toDateString(), $sampai->toDateString()])
            ->sum('biaya');

        $hari = $jumlah(today(), today());
        $bulan = $jumlah(now()->startOfMonth(), today());
        $lalu = now()->subMonthNoOverflow();
        $bulanLalu = $jumlah($lalu->copy()->startOfMonth(), $lalu->copy()->endOfMonth());

        $total = (int) $diambil()->sum('biaya');
        $berbiaya = $diambil()->whereNotNull('biaya')->count();

        // ---- Januari sampai Desember tahun ini ----
        $mulaiBulan = now()->startOfYear();
        $perBulan = $diambil()
            ->whereBetween('tanggal_diambil', [$mulaiBulan->toDateString(), today()->toDateString()])
            ->get(['tanggal_diambil', 'biaya'])
            ->groupBy(fn ($s) => $s->tanggal_diambil->format('Y-m'))
            ->map(fn ($g) => (int) $g->sum('biaya'));

        $bulanan = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $mulaiBulan->copy()->addMonths($i);
            $n = (int) ($perBulan[$m->format('Y-m')] ?? 0);
            $bulanan[] = [
                'label'  => $m->locale('id')->translatedFormat('M'),
                'depan'  => $m->gt(now()->endOfMonth()),   // bulan yang belum tiba
                'tip'    => $m->locale('id')->translatedFormat('F Y') . ': ' . Format::rupiah($n),
                'jumlah' => $n,
                'kini'   => $m->isSameMonth(now()),
            ];
        }

        $maks = max(1, max(array_column($bulanan, 'jumlah')));
        $bulanan = array_map(fn ($d) => $d + ['persen' => max(2, (int) round($d['jumlah'] / $maks * 100))], $bulanan);

        return [
            'uang' => [
                'hari'           => $hari,
                'bulan'          => $bulan,
                'pct'            => Format::persen($bulan, $bulanLalu),
                'total'          => $total,
                'rata'           => $berbiaya ? (int) round($total / $berbiaya) : 0,
                'tanpaBiaya'     => $diambil()->whereNull('biaya')->count(),
                'tahun'          => array_sum(array_column($bulanan, 'jumlah')),
                // motor yang sudah selesai tetapi belum diambil: belum dihitung sebagai pendapatan
                'tertahan'       => (int) Servis::where('status', 'selesai')->sum('biaya'),
                'tertahanJumlah' => Servis::where('status', 'selesai')->whereNotNull('biaya')->count(),
            ],
            'uangBulanan' => $bulanan,
        ];
    }

    private function dataOwner(): array
    {
        $mulai = now()->startOfMonth()->subMonths(5);
        $perBulan = Servis::where('tanggal_masuk', '>=', $mulai)->get(['tanggal_masuk'])
            ->countBy(fn ($s) => $s->tanggal_masuk->format('Y-m'));

        $bulan = [];
        for ($i = 0; $i < 6; $i++) {
            $m = $mulai->copy()->addMonths($i);
            $bulan[] = [
                'label'  => $m->locale('id')->translatedFormat('M'),
                'jumlah' => (int) ($perBulan[$m->format('Y-m')] ?? 0),
            ];
        }

        $rata = Servis::whereNotNull('tanggal_selesai')->get(['tanggal_masuk', 'tanggal_selesai'])
            ->avg(fn ($s) => abs($s->tanggal_masuk->diffInDays($s->tanggal_selesai)));

        $langganan = Servis::daftarLangganan();

        return [
            'langgananJumlah' => $langganan->count(),
            'langgananTeratas' => $langganan->take(5),
            'bulan'     => $bulan,
            'maks'      => max(1, max(array_column($bulan, 'jumlah'))),
            'rata'      => $rata,
            'aktivitas' => AuditLog::with('user')->latest('id')->limit(7)->get(),
            'resetMenunggu' => \App\Models\PermintaanResetSandi::menunggu()->count(),
        ];
    }
}
