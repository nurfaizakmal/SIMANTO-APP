<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatus extends Model
{
    protected $table = 'riwayat_status';

    protected $fillable = ['servis_id', 'user_id', 'dari_status', 'ke_status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class);
    }

    public function labelKe(): string
    {
        return Servis::STATUS[$this->ke_status] ?? $this->ke_status;
    }

    public function deskripsi(): string
    {
        if (!$this->dari_status) {
            return 'Dicatat masuk: ' . $this->labelKe();
        }
        $dari = Servis::STATUS[$this->dari_status] ?? $this->dari_status;
        return $dari . ' → ' . $this->labelKe();
    }
}
