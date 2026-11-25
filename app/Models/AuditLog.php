<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    public $timestamps = false;

    protected $fillable = ['user_id', 'aksi', 'keterangan', 'ip', 'perangkat', 'created_at'];

    public const LABEL = [
        'login'          => 'Login',
        'login_gagal'    => 'Login gagal',
        'logout'         => 'Logout',
        'buat_servis'    => 'Motor masuk',
        'ubah_status'    => 'Ubah status',
        'ubah_servis'    => 'Ubah data servis',
        'hapus_servis'   => 'Hapus servis',
        'buat_pengguna'  => 'Tambah pengguna',
        'ubah_pengguna'  => 'Ubah pengguna',
        'hapus_pengguna' => 'Hapus pengguna',
        'minta_reset_sandi' => 'Minta reset password',
        'reset_sandi'    => 'Reset password',
        'reset_sandi_gagal' => 'Reset password gagal',
        'setujui_reset_sandi' => 'Setujui reset password',
        'tolak_reset_sandi' => 'Tolak reset password',
        'reset_sandi_owner' => 'Reset password owner',
        'buat_cadangan'  => 'Unduh cadangan',
        'pulihkan_data'  => 'Pulihkan data',
        'pulihkan_gagal' => 'Pulihkan data gagal',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Catat satu kejadian. Kegagalan mencatat tidak boleh merusak aksi utama. */
    public static function catat(string $aksi, ?string $keterangan = null, ?int $userId = null): void
    {
        try {
            static::create([
                'user_id'    => $userId ?? auth()->id(),
                'aksi'       => $aksi,
                'keterangan' => $keterangan ? Str::limit($keterangan, 480) : null,
                'ip'         => request()->ip(),
                'perangkat'  => Str::limit((string) request()->userAgent(), 250, ''),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function label(): string
    {
        return self::LABEL[$this->aksi] ?? $this->aksi;
    }

    public function warna(): string
    {
        return match (true) {
            str_contains($this->aksi, 'hapus'), str_contains($this->aksi, 'gagal') => '#b42318',
            str_starts_with($this->aksi, 'ubah')  => '#1971c2',
            str_starts_with($this->aksi, 'buat')  => '#2f9e44',
            default                               => '#6b7a87',
        };
    }
}
