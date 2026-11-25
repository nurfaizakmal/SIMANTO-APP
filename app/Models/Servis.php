<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Support\FotoMotor;

class Servis extends Model
{
    protected $table = 'servis';

    protected $fillable = [
        'nama_pelanggan', 'no_hp', 'plat_nomor', 'merk_motor', 'foto', 'biaya',
        'keluhan', 'status', 'catatan', 'tanggal_masuk', 'tanggal_selesai',
    ];

    public const STATUS = [
        'menunggu'           => 'Menunggu antrian',
        'dikerjakan'         => 'Sedang dikerjakan',
        'menunggu_sparepart' => 'Menunggu sparepart',
        'selesai'            => 'Selesai, siap diambil',
        'diambil'            => 'Sudah diambil',
    ];

    protected function casts(): array
    {
        return [
            // 'encrypted' memakai Crypt Laravel = AES-256-CBC (cipher di config/app.php) + MAC
            'plat_nomor'      => 'encrypted',
            'tanggal_masuk'   => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_diambil' => 'date',
            'biaya'           => 'integer',
        ];
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class)->orderBy('created_at')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::creating(function (Servis $s) {
            do {
                $kode = 'SRV-' . strtoupper(Str::random(6));
            } while (static::where('kode_servis', $kode)->exists());
            $s->kode_servis = $kode;
        });

        static::saving(function (Servis $s) {
            if ($s->isDirty('plat_nomor')) {
                $s->plat_nomor = strtoupper(trim($s->plat_nomor));
                $s->plat_nomor_hash = static::hashPlat($s->plat_nomor);
            }
            // Biaya baru dihitung sebagai pendapatan owner ketika motor sudah DIAMBIL.
            if ($s->isDirty('status')) {
                $s->tanggal_diambil = $s->status === 'diambil' ? now() : null;
                if ($s->status === 'diambil' && !$s->tanggal_selesai) {
                    $s->tanggal_selesai = now();
                }
            }
            if ($s->status === 'selesai' && !$s->tanggal_selesai) {
                $s->tanggal_selesai = now();
            }
        });

        // ---- riwayat status + audit log ----
        static::created(function (Servis $s) {
            RiwayatStatus::create([
                'servis_id'   => $s->id,
                'user_id'     => auth()->id(),
                'dari_status' => null,
                'ke_status'   => $s->status ?? 'menunggu',
            ]);
            AuditLog::catat('buat_servis', "Motor masuk {$s->kode_servis} ({$s->merk_motor})");
        });

        static::updated(function (Servis $s) {
            if ($s->wasChanged('status')) {
                $dari = $s->getOriginal('status');
                RiwayatStatus::create([
                    'servis_id'   => $s->id,
                    'user_id'     => auth()->id(),
                    'dari_status' => $dari,
                    'ke_status'   => $s->status,
                ]);
                AuditLog::catat('ubah_status',
                    "{$s->kode_servis}: " . (self::STATUS[$dari] ?? $dari) . ' → ' . $s->labelStatus());
            }

            // Hanya nama field yang dicatat, bukan isinya (plat nomor tidak bocor ke log).
            $lain = array_diff(array_keys($s->getChanges()),
                ['status', 'updated_at', 'tanggal_selesai', 'tanggal_diambil', 'plat_nomor_hash']);
            if ($lain) {
                AuditLog::catat('ubah_servis', "{$s->kode_servis}: mengubah " . implode(', ', $lain));
            }
        });

        static::deleted(function (Servis $s) {
            FotoMotor::hapus($s->foto);
            AuditLog::catat('hapus_servis', "Menghapus {$s->kode_servis} ({$s->merk_motor})");
        });
    }

    /** Jumlah kunjungan minimal agar sebuah plat menjadi langganan (lebih dari 3 kali = 4). */
    public static function batasLangganan(): int
    {
        return max(2, (int) config('bengkel.langganan_minimal_kunjungan', 4));
    }

    /**
     * Jumlah kunjungan per plat untuk banyak plat sekaligus (1 query).
     * @param  iterable<string>  $hashes  nilai plat_nomor_hash
     * @return \Illuminate\Support\Collection  [hash => jumlah]
     */
    public static function hitungKunjungan(iterable $hashes): \Illuminate\Support\Collection
    {
        $hashes = collect($hashes)->filter()->unique()->values();
        if ($hashes->isEmpty()) {
            return collect();
        }

        return static::whereIn('plat_nomor_hash', $hashes)
            ->selectRaw('plat_nomor_hash, count(*) as n')
            ->groupBy('plat_nomor_hash')
            ->pluck('n', 'plat_nomor_hash');
    }

    /**
     * Semua motor langganan (plat sama >= batas kunjungan), terbanyak dulu.
     * @return \Illuminate\Support\Collection  daftar objek {servis, jumlah, terakhir}
     */
    public static function daftarLangganan(): \Illuminate\Support\Collection
    {
        $grup = static::query()
            ->selectRaw('plat_nomor_hash, count(*) as jumlah, max(id) as id_terakhir, max(tanggal_masuk) as terakhir_masuk')
            ->groupBy('plat_nomor_hash')
            ->havingRaw('count(*) >= ?', [static::batasLangganan()])
            ->orderByDesc('jumlah')
            ->orderByDesc('terakhir_masuk')
            ->get();

        $terakhir = static::whereIn('id', $grup->pluck('id_terakhir'))->get()->keyBy('id');

        return $grup->map(fn ($g) => isset($terakhir[$g->id_terakhir]) ? (object) [
            'servis'   => $terakhir[$g->id_terakhir],
            'jumlah'   => (int) $g->jumlah,
            'terakhir' => $terakhir[$g->id_terakhir]->tanggal_masuk,
        ] : null)->filter()->values();
    }

    /** Apakah motor ini langganan? (hitung jumlah kunjungan plat yang sama) */
    public function jumlahKunjungan(): int
    {
        return (int) static::where('plat_nomor_hash', $this->plat_nomor_hash)->count();
    }

    public function langganan(): bool
    {
        return $this->jumlahKunjungan() >= static::batasLangganan();
    }

    /** Hapus spasi/simbol, huruf besar: "ab 1234 cd" => "AB1234CD" */
    public static function normalisasiPlat(string $plat): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plat));
    }

    /** Blind index: hash deterministik agar plat terenkripsi tetap bisa dicari. */
    public static function hashPlat(string $plat): string
    {
        return hash_hmac('sha256', static::normalisasiPlat($plat), config('app.key'));
    }

    /** Plat tersamar untuk tampilan publik: "AB*****D" */
    public function platSamar(): string
    {
        $p = static::normalisasiPlat($this->plat_nomor);
        return substr($p, 0, 2) . str_repeat('*', max(strlen($p) - 3, 0)) . substr($p, -1);
    }

    /** URL foto motor (mengikuti host yang sedang dipakai), atau null bila belum ada foto. */
    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
