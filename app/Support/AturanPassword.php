<?php

namespace App\Support;

use Closure;

/**
 * Aturan password untuk semua tempat password dibuat atau diganti:
 * minimal 8 karakter, mengandung huruf, angka, dan simbol khusus.
 */
class AturanPassword
{
    public const MINIMAL = 8;

    /** Daftar syarat yang belum terpenuhi (kosong = password sudah sesuai). */
    public static function kekurangan(string $password): array
    {
        $k = [];
        if (mb_strlen($password) < self::MINIMAL) {
            $k[] = 'minimal ' . self::MINIMAL . ' karakter';
        }
        if (!preg_match('/\p{L}/u', $password)) {
            $k[] = 'huruf';
        }
        if (!preg_match('/\p{N}/u', $password)) {
            $k[] = 'angka';
        }
        // simbol = karakter selain huruf, angka, dan spasi
        if (!preg_match('/[^\p{L}\p{N}\s]/u', $password)) {
            $k[] = 'simbol khusus (misalnya ! @ # $ % & *)';
        }
        return $k;
    }

    public static function memenuhi(string $password): bool
    {
        return self::kekurangan($password) === [];
    }

    /** Aturan validasi Laravel dengan pesan berbahasa Indonesia. */
    public static function rule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $kurang = self::kekurangan((string) $value);
            if ($kurang) {
                $fail('Password harus berisi ' . implode(', ', $kurang) . '.');
            }
        };
    }
}
