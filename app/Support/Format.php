<?php

namespace App\Support;

class Format
{
    /** Rp 85.000 */
    public static function rupiah(int|float|null $n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    /** Rp 18,4 Jt / Rp 1,28 M / Rp 85.000 (untuk kartu angka). */
    public static function rupiahSingkat(int|float|null $n): string
    {
        $n = (float) $n;
        if ($n >= 1_000_000_000) {
            return 'Rp ' . rtrim(rtrim(number_format($n / 1_000_000_000, 2, ',', '.'), '0'), ',') . ' M';
        }
        if ($n >= 1_000_000) {
            return 'Rp ' . rtrim(rtrim(number_format($n / 1_000_000, 1, ',', '.'), '0'), ',') . ' Jt';
        }
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /** 85rb / 1,2jt / 0 (untuk label grafik yang sempit). */
    public static function singkat(int|float|null $n): string
    {
        $n = (float) $n;
        if ($n >= 1_000_000_000) {
            return rtrim(rtrim(number_format($n / 1_000_000_000, 1, ',', '.'), '0'), ',') . 'M';
        }
        if ($n >= 1_000_000) {
            return rtrim(rtrim(number_format($n / 1_000_000, 1, ',', '.'), '0'), ',') . 'jt';
        }
        if ($n >= 1_000) {
            return number_format($n / 1_000, 0, ',', '.') . 'rb';
        }
        return (string) (int) $n;
    }

    /** Perubahan dalam persen, null bila tidak ada pembanding. */
    public static function persen(float $sekarang, float $sebelum): ?int
    {
        if ($sebelum <= 0) {
            return null;
        }
        return (int) round(($sekarang - $sebelum) / $sebelum * 100);
    }
}
