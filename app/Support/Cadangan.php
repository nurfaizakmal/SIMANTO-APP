<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Servis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Cadangan & pemulihan data bengkel.
 *
 * Isi berkas cadangan (.zip):
 *   manifest.json            versi, waktu, jumlah data, checksum SHA-256 tiap berkas
 *   manifest.sig             tanda tangan HMAC-SHA256 manifest memakai APP_KEY
 *   data/servis.ndjson       satu baris JSON per servis (plat nomor tetap terenkripsi)
 *   data/riwayat_status.ndjson
 *   foto/<nama>.jpg          foto motor
 *
 *   data/audit_log.ndjson    arsip audit log (petugas disimpan sebagai email)
 *   data/users.ndjson        HANYA pada cadangan "lengkap" (buatan owner): akun beserta hash password
 *
 * Cadangan standar (admin & owner) tidak memuat akun/hash password. Permintaan reset password tidak pernah ikut.
 * Saat pemulihan: audit log TIDAK ditimpa; hanya entri yang lebih tua dari entri tertua saat ini yang ditambahkan.
 * Akun hanya dipulihkan dari cadangan lengkap, atas permintaan eksplisit owner (tambah/perbarui per email, tidak
 * ada akun yang dihapus, akun yang sedang dipakai tidak diubah).
 *
 * Keamanan pemulihan: berkas harus bertanda tangan APP_KEY yang sama (cadangan palsu/diubah ditolak),
 * checksum semua berkas diperiksa, nama berkas di dalam zip dibatasi, dan seluruh perubahan database
 * berjalan dalam satu transaksi (gagal di tengah = data lama utuh).
 */
class Cadangan
{
    public const VERSI = 1;
    public const MAKS_BYTE = 1_500_000_000;   // total ukuran isi zip setelah dibuka

    private const POLA_FOTO       = '#^servis/[A-Za-z0-9_-]{1,64}\.(jpe?g|png|webp)$#i';
    private const POLA_ENTRI_FOTO = '#^foto/[A-Za-z0-9_-]{1,64}\.(jpe?g|png|webp)$#i';
    private const DATA = ['data/servis.ndjson', 'data/riwayat_status.ndjson'];
    private const OPSIONAL = ['data/audit_log.ndjson', 'data/users.ndjson'];
    private const ROLE = ['teknisi', 'admin', 'owner'];
    private const WAJIB_SERVIS = ['id', 'kode_servis', 'nama_pelanggan', 'plat_nomor', 'plat_nomor_hash',
                                  'merk_motor', 'keluhan', 'status', 'tanggal_masuk'];

    /** Sidik jari kunci (bukan kunci itu sendiri) untuk mendeteksi cadangan dari APP_KEY lain. */
    public static function sidikKunci(): string
    {
        return substr(hash_hmac('sha256', 'bengkel-motor-cadangan', (string) config('app.key')), 0, 16);
    }

    private static function tandaTangan(string $isi): string
    {
        return hash_hmac('sha256', $isi, (string) config('app.key'));
    }

    // ------------------------------------------------------------------ BUAT CADANGAN

    /** Tulis cadangan ke $tujuan (path .zip). Mengembalikan jumlah data yang tersimpan. */
    public static function buat(string $tujuan, ?User $oleh = null, bool $lengkap = false): array
    {
        $dir = dirname($tujuan);
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new \RuntimeException('Folder cadangan tidak bisa dibuat.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tujuan, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Berkas cadangan tidak bisa dibuat.');
        }

        $sementara = [];
        $json = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;

        try {
            $berkas = [];
            $jumlah = ['servis' => 0, 'riwayat_status' => 0, 'audit' => 0, 'pengguna' => 0, 'foto' => 0, 'foto_hilang' => 0];
            $foto = [];

            // --- servis
            $f = self::sementara($sementara);
            $h = fopen($f, 'wb');
            foreach (DB::table('servis')->orderBy('id')->cursor() as $r) {
                $baris = (array) $r;
                fwrite($h, json_encode($baris, $json) . "\n");
                $jumlah['servis']++;
                if (!empty($baris['foto']) && preg_match(self::POLA_FOTO, $baris['foto'])) {
                    $foto[$baris['foto']] = true;
                }
            }
            fclose($h);
            $zip->addFile($f, 'data/servis.ndjson');
            $berkas['data/servis.ndjson'] = hash_file('sha256', $f);

            // --- riwayat status (petugas disimpan sebagai email, bukan id)
            $f = self::sementara($sementara);
            $h = fopen($f, 'wb');
            $q = DB::table('riwayat_status as r')
                ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
                ->orderBy('r.id')->select('r.*', 'u.email as user_email');
            foreach ($q->cursor() as $r) {
                $baris = (array) $r;
                unset($baris['user_id']);
                fwrite($h, json_encode($baris, $json) . "\n");
                $jumlah['riwayat_status']++;
            }
            fclose($h);
            $zip->addFile($f, 'data/riwayat_status.ndjson');
            $berkas['data/riwayat_status.ndjson'] = hash_file('sha256', $f);

            // --- audit log (arsip)
            $f = self::sementara($sementara);
            $h = fopen($f, 'wb');
            $q = DB::table('audit_log as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                ->orderBy('a.id')->select('a.*', 'u.email as user_email');
            foreach ($q->cursor() as $r) {
                $baris = (array) $r;
                unset($baris['id'], $baris['user_id']);
                fwrite($h, json_encode($baris, $json) . "\n");
                $jumlah['audit']++;
            }
            fclose($h);
            $zip->addFile($f, 'data/audit_log.ndjson');
            $berkas['data/audit_log.ndjson'] = hash_file('sha256', $f);

            // --- akun pengguna (hanya cadangan lengkap; berisi hash password)
            if ($lengkap) {
                $f = self::sementara($sementara);
                $h = fopen($f, 'wb');
                foreach (DB::table('users')->orderBy('id')->cursor() as $r) {
                    $baris = (array) $r;
                    unset($baris['id'], $baris['remember_token']);
                    fwrite($h, json_encode($baris, $json) . "\n");
                    $jumlah['pengguna']++;
                }
                fclose($h);
                $zip->addFile($f, 'data/users.ndjson');
                $berkas['data/users.ndjson'] = hash_file('sha256', $f);
            }

            // --- foto
            $disk = Storage::disk('public');
            foreach (array_keys($foto) as $path) {
                if (!$disk->exists($path)) {
                    $jumlah['foto_hilang']++;
                    continue;
                }
                $entri = 'foto/' . basename($path);
                $zip->addFile($disk->path($path), $entri);
                $berkas[$entri] = hash_file('sha256', $disk->path($path));
                $jumlah['foto']++;
            }

            $manifest = json_encode([
                'versi'      => self::VERSI,
                'jenis'      => $lengkap ? 'lengkap' : 'standar',
                'aplikasi'   => config('app.name'),
                'dibuat_pada' => now()->toIso8601String(),
                'dibuat_oleh' => $oleh?->email,
                'sidik_kunci' => self::sidikKunci(),
                'jumlah'     => $jumlah,
                'berkas'     => $berkas,
            ], $json | JSON_PRETTY_PRINT);

            $zip->addFromString('manifest.json', $manifest);
            $zip->addFromString('manifest.sig', self::tandaTangan($manifest));

            if (!$zip->close()) {
                throw new \RuntimeException('Gagal menulis berkas cadangan (disk penuh?).');
            }
            $zip = null;

            return $jumlah;
        } catch (\Throwable $e) {
            if ($zip instanceof ZipArchive) {
                @$zip->close();
            }
            @unlink($tujuan);
            throw $e;
        } finally {
            foreach ($sementara as $f) {
                @unlink($f);
            }
        }
    }

    private static function sementara(array &$daftar): string
    {
        $f = tempnam(sys_get_temp_dir(), 'cdg');
        if ($f === false) {
            throw new \RuntimeException('Berkas sementara tidak bisa dibuat.');
        }
        return $daftar[] = $f;
    }

    // ------------------------------------------------------------------ PULIHKAN

    /**
     * Ganti SELURUH data servis, riwayat status, dan foto dengan isi cadangan.
     * Sebelumnya data sekarang disimpan dulu ke storage/app/cadangan/sebelum-pulih-*.zip.
     * Pesan DomainException aman ditampilkan ke pengguna.
     */
    public static function pulihkan(string $zipPath, User $oleh, bool $denganPengguna = false): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \DomainException('Berkas bukan zip yang valid.');
        }

        try {
            $manifest = self::periksa($zip);
            $adaAudit = isset($manifest['berkas']['data/audit_log.ndjson']);
            $adaPengguna = isset($manifest['berkas']['data/users.ndjson']);
            if ($denganPengguna && !$adaPengguna) {
                throw new \DomainException('Cadangan ini bukan cadangan lengkap, tidak ada data akun pengguna di dalamnya.');
            }
            $fotoAda = [];
            foreach ($manifest['berkas'] as $nama => $_) {
                if (preg_match(self::POLA_ENTRI_FOTO, $nama)) {
                    $fotoAda['servis/' . substr($nama, 5)] = true;
                }
            }

            // Lolos-uji dulu seluruh isi sebelum ada yang disentuh.
            $idServis = [];
            foreach (self::baris($zip, 'data/servis.ndjson') as $r) {
                foreach (self::WAJIB_SERVIS as $k) {
                    if (!isset($r[$k]) || $r[$k] === '') {
                        throw new \DomainException("Data servis rusak: kolom {$k} kosong.");
                    }
                }
                if (!is_int($r['id']) || $r['id'] < 1 || isset($idServis[$r['id']])) {
                    throw new \DomainException('Data servis rusak: id tidak valid atau ganda.');
                }
                if (!isset(Servis::STATUS[$r['status']])) {
                    throw new \DomainException('Data servis rusak: status tidak dikenal.');
                }
                $idServis[$r['id']] = true;
            }
            foreach (self::baris($zip, 'data/riwayat_status.ndjson') as $r) {
                if (!isset($r['servis_id'], $r['ke_status'])) {
                    throw new \DomainException('Data riwayat rusak.');
                }
            }
            if ($denganPengguna) {
                foreach (self::baris($zip, 'data/users.ndjson') as $r) {
                    if (empty($r['email']) || empty($r['name']) || empty($r['password'])
                        || !in_array($r['role'] ?? null, self::ROLE, true)) {
                        throw new \DomainException('Data akun pengguna di cadangan rusak.');
                    }
                }
            }

            // Pengaman: simpan kondisi sekarang agar pemulihan yang keliru masih bisa dibatalkan.
            $pengaman = storage_path('app/cadangan/sebelum-pulih-' . now()->format('Ymd-His') . '.zip');
            self::buat($pengaman, $oleh, $denganPengguna);

            $kolomServis = array_flip(Schema::getColumnListing('servis'));
            $kolomRiwayat = array_flip(Schema::getColumnListing('riwayat_status'));
            $kolomAudit = array_flip(Schema::getColumnListing('audit_log'));
            $kolomUser = array_flip(Schema::getColumnListing('users'));
            $fotoDipakai = [];
            $nServis = $nRiwayat = $nAudit = $nPengguna = 0;

            DB::transaction(function () use ($zip, $oleh, $denganPengguna, $adaAudit, $kolomServis, $kolomRiwayat, $kolomAudit, $kolomUser, $fotoAda, $idServis, &$fotoDipakai, &$nServis, &$nRiwayat, &$nAudit, &$nPengguna) {
                // Akun dulu (bila diminta), supaya petugas di riwayat/audit bisa dicocokkan ke akun yang benar.
                if ($denganPengguna) {
                    foreach (self::baris($zip, 'data/users.ndjson') as $r) {
                        if ($r['email'] === $oleh->email) {
                            continue;   // akun yang sedang dipakai tidak disentuh (cegah terkunci / turun role)
                        }
                        $d = array_intersect_key($r, $kolomUser);
                        unset($d['id'], $d['remember_token']);
                        $ada = DB::table('users')->where('email', $r['email'])->exists();
                        if ($ada) {
                            DB::table('users')->where('email', $r['email'])->update($d);
                        } else {
                            DB::table('users')->insert($d);
                        }
                        $nPengguna++;
                    }
                }
                $pengguna = DB::table('users')->pluck('id', 'email')->all();

                DB::table('riwayat_status')->delete();
                DB::table('servis')->delete();

                $kumpul = [];
                foreach (self::baris($zip, 'data/servis.ndjson') as $r) {
                    $foto = $r['foto'] ?? null;
                    $r['foto'] = ($foto && preg_match(self::POLA_FOTO, $foto) && isset($fotoAda[$foto])) ? $foto : null;
                    if ($r['foto']) {
                        $fotoDipakai[$r['foto']] = true;
                    }
                    $kumpul[] = array_intersect_key($r, $kolomServis);
                    $nServis++;
                    if (count($kumpul) >= 200) {
                        DB::table('servis')->insert($kumpul);
                        $kumpul = [];
                    }
                }
                if ($kumpul) {
                    DB::table('servis')->insert($kumpul);
                }

                $kumpul = [];
                foreach (self::baris($zip, 'data/riwayat_status.ndjson') as $r) {
                    if (!isset($idServis[$r['servis_id']])) {
                        continue;
                    }
                    $r['user_id'] = isset($r['user_email']) ? ($pengguna[$r['user_email']] ?? null) : null;
                    $kumpul[] = array_intersect_key($r, $kolomRiwayat);
                    $nRiwayat++;
                    if (count($kumpul) >= 200) {
                        DB::table('riwayat_status')->insert($kumpul);
                        $kumpul = [];
                    }
                }
                if ($kumpul) {
                    DB::table('riwayat_status')->insert($kumpul);
                }

                // Audit log: tidak pernah ditimpa/dihapus. Hanya entri yang LEBIH TUA dari entri tertua
                // saat ini yang ditambahkan (memulihkan arsip), sehingga memulihkan berulang tidak menggandakan.
                if ($adaAudit) {
                    $tertua = DB::table('audit_log')->min('created_at');
                    $kumpul = [];
                    foreach (self::baris($zip, 'data/audit_log.ndjson') as $r) {
                        if (empty($r['created_at']) || ($tertua !== null && (string) $r['created_at'] >= (string) $tertua)) {
                            continue;
                        }
                        $r['user_id'] = isset($r['user_email']) ? ($pengguna[$r['user_email']] ?? null) : null;
                        unset($r['id']);
                        $kumpul[] = array_intersect_key($r, $kolomAudit);
                        $nAudit++;
                        if (count($kumpul) >= 200) {
                            DB::table('audit_log')->insert($kumpul);
                            $kumpul = [];
                        }
                    }
                    if ($kumpul) {
                        DB::table('audit_log')->insert($kumpul);
                    }
                }

                if (DB::getDriverName() === 'pgsql') {
                    foreach (['servis', 'riwayat_status', 'audit_log'] as $t) {
                        DB::statement("select setval(pg_get_serial_sequence('{$t}','id'), coalesce(max(id),1), max(id) is not null) from {$t}");
                    }
                }
            });

            // Foto: dikerjakan setelah database berhasil, lalu buang foto yang tidak lagi dipakai.
            $disk = Storage::disk('public');
            foreach (array_keys($fotoDipakai) as $path) {
                $s = $zip->getStream('foto/' . basename($path));
                if ($s) {
                    $disk->writeStream($path, $s);
                    fclose($s);
                }
            }
            foreach ($disk->files('servis') as $f) {
                if (!isset($fotoDipakai[$f])) {
                    $disk->delete($f);
                }
            }

            $dibuat = Carbon::parse($manifest['dibuat_pada'])->timezone(config('app.timezone'))->format('d/m/Y H:i');
            AuditLog::catat('pulihkan_data',
                "Cadangan {$dibuat}: {$nServis} servis, {$nRiwayat} riwayat, " . count($fotoDipakai) . " foto, {$nAudit} arsip audit"
                . ($denganPengguna ? ", {$nPengguna} akun" : '') . '. Pengaman: ' . basename($pengaman));

            return [
                'servis' => $nServis, 'riwayat' => $nRiwayat, 'foto' => count($fotoDipakai),
                'audit' => $nAudit, 'pengguna' => $nPengguna, 'denganPengguna' => $denganPengguna,
                'dibuat_pada' => $dibuat, 'pengaman' => basename($pengaman),
            ];
        } finally {
            $zip->close();
        }
    }

    /** Pastikan zip asli, utuh, dan ditandatangani APP_KEY ini. Mengembalikan manifest. */
    private static function periksa(ZipArchive $zip): array
    {
        $total = 0;
        $nama = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $st = $zip->statIndex($i);
            $n = $st['name'];
            if (str_ends_with($n, '/')) {
                continue;
            }
            $boleh = in_array($n, ['manifest.json', 'manifest.sig'], true)
                || in_array($n, self::DATA, true)
                || in_array($n, self::OPSIONAL, true)
                || preg_match(self::POLA_ENTRI_FOTO, $n);
            if (!$boleh) {
                throw new \DomainException('Berkas cadangan berisi file yang tidak dikenal dan ditolak.');
            }
            $total += $st['size'];
            $nama[$n] = true;
        }
        if ($total > self::MAKS_BYTE) {
            throw new \DomainException('Isi cadangan terlalu besar.');
        }
        if (!isset($nama['manifest.json'], $nama['manifest.sig'])) {
            throw new \DomainException('Ini bukan berkas cadangan bengkel (manifest tidak ada).');
        }

        $isi = $zip->getFromName('manifest.json');
        $tanda = trim((string) $zip->getFromName('manifest.sig'));
        $m = json_decode((string) $isi, true);
        if (!is_array($m) || !isset($m['versi'], $m['berkas'], $m['dibuat_pada'], $m['sidik_kunci']) || !is_array($m['berkas'])) {
            throw new \DomainException('Manifest cadangan rusak.');
        }
        if (!hash_equals(self::sidikKunci(), (string) $m['sidik_kunci'])) {
            throw new \DomainException('Cadangan ini dibuat dengan APP_KEY yang berbeda, sehingga plat nomor tidak bisa dibaca. Pasang APP_KEY lama lalu coba lagi.');
        }
        if (!hash_equals(self::tandaTangan((string) $isi), $tanda)) {
            throw new \DomainException('Tanda tangan cadangan tidak cocok. Berkas rusak atau sudah diubah.');
        }
        if ((int) $m['versi'] > self::VERSI) {
            throw new \DomainException('Cadangan dibuat oleh versi aplikasi yang lebih baru.');
        }

        // Setiap berkas di zip harus terdaftar di manifest & checksum-nya cocok, dan sebaliknya.
        foreach (self::DATA as $d) {
            if (!isset($m['berkas'][$d])) {
                throw new \DomainException("Cadangan tidak lengkap ({$d} tidak ada).");
            }
        }
        foreach ($nama as $n => $_) {
            if (in_array($n, ['manifest.json', 'manifest.sig'], true)) {
                continue;
            }
            if (!isset($m['berkas'][$n])) {
                throw new \DomainException('Cadangan berisi file yang tidak terdaftar di manifest.');
            }
        }
        foreach ($m['berkas'] as $n => $sha) {
            if (!isset($nama[$n]) || !self::hashEntri($zip, $n, (string) $sha)) {
                throw new \DomainException("Berkas {$n} rusak atau tidak ada (checksum tidak cocok).");
            }
        }

        return $m;
    }

    private static function hashEntri(ZipArchive $zip, string $nama, string $harapan): bool
    {
        $s = $zip->getStream($nama);
        if (!$s) {
            return false;
        }
        $ctx = hash_init('sha256');
        while (!feof($s)) {
            hash_update($ctx, (string) fread($s, 1 << 20));
        }
        fclose($s);

        return hash_equals($harapan, hash_final($ctx));
    }

    /** Baca berkas NDJSON dari zip, satu baris satu array (hemat memori). */
    private static function baris(ZipArchive $zip, string $nama): \Generator
    {
        $s = $zip->getStream($nama);
        if (!$s) {
            throw new \DomainException("Berkas {$nama} tidak bisa dibaca.");
        }
        try {
            while (($l = fgets($s)) !== false) {
                $l = trim($l);
                if ($l === '') {
                    continue;
                }
                $d = json_decode($l, true);
                if (!is_array($d)) {
                    throw new \DomainException("Isi {$nama} rusak.");
                }
                yield $d;
            }
        } finally {
            fclose($s);
        }
    }
}
