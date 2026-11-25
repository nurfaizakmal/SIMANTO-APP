<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeResetSandi extends Model
{
    protected $table = 'kode_reset_sandi';

    protected $fillable = ['email', 'kode', 'percobaan', 'kadaluarsa_at'];

    protected $hidden = ['kode'];

    protected function casts(): array
    {
        return ['kadaluarsa_at' => 'datetime'];
    }
}
