<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FotoMotor
{
    private const SISI_MAKS = 1280;   // piksel, sisi terpanjang

    /**
     * Simpan foto ke disk "public" dan kembalikan path relatif (mis. servis/abc.jpg).
     * Foto dibuat ulang sebagai JPEG: data EXIF (termasuk koordinat GPS dari HP) terbuang,
     * arah foto dikoreksi, dan ukuran dikecilkan agar halaman depan tetap ringan.
     */
    public static function simpan(UploadedFile $file): string
    {
        if (function_exists('imagecreatefromstring')) {
            try {
                $jpeg = self::proses($file->getRealPath(), $file->getMimeType());
                if ($jpeg !== null) {
                    $nama = 'servis/' . Str::random(32) . '.jpg';
                    Storage::disk('public')->put($nama, $jpeg);
                    return $nama;
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Cadangan bila ekstensi GD tidak aktif: simpan apa adanya dengan nama acak.
        return (string) $file->store('servis', 'public');
    }

    public static function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private static function proses(string $path, ?string $mime): ?string
    {
        $mentah = file_get_contents($path);
        $gambar = $mentah === false ? false : @imagecreatefromstring($mentah);
        if ($gambar === false) {
            return null;
        }

        // Koreksi arah dari EXIF (foto HP sering tersimpan "miring").
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $arah = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;
            $putar = match ($arah) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
            if ($putar !== 0) {
                $baru = imagerotate($gambar, $putar, 0);
                if ($baru !== false) {
                    $gambar = $baru;
                }
            }
        }

        $w = imagesx($gambar);
        $h = imagesy($gambar);
        $skala = min(1, self::SISI_MAKS / max($w, $h));
        $nw = max(1, (int) round($w * $skala));
        $nh = max(1, (int) round($h * $skala));

        // Latar putih supaya PNG/WebP transparan tidak jadi hitam saat menjadi JPEG.
        $kanvas = imagecreatetruecolor($nw, $nh);
        imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255));
        imagecopyresampled($kanvas, $gambar, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagejpeg($kanvas, null, 82);
        $jpeg = ob_get_clean();

        return ($jpeg === false || $jpeg === '') ? null : $jpeg;
    }
}
