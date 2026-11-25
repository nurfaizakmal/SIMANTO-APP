<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanResetSandi extends Model
{
    protected $table = 'permintaan_reset_sandi';

    protected $fillable = ['user_id', 'password', 'status', 'ip', 'perangkat', 'diproses_oleh', 'diproses_at'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['diproses_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /** Permintaan yang masih menunggu dan belum kedaluwarsa. */
    public function scopeMenunggu(Builder $q): Builder
    {
        return $q->where('status', 'menunggu')
            ->where('created_at', '>=', now()->subDays((int) config('bengkel.reset_berlaku_hari', 3)));
    }

    public function masihBerlaku(): bool
    {
        return $this->status === 'menunggu'
            && $this->created_at->gte(now()->subDays((int) config('bengkel.reset_berlaku_hari', 3)));
    }
}
