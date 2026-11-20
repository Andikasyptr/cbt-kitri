<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Soal extends Model
{
    protected $table = 'soals';
    protected $guarded = [];

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function gambar(): HasOne
    {
        return $this->hasOne(SoalGambar::class);
    }

    // Daftar opsi sebagai array: ['A' => '...', 'B' => '...', ...]
    public function getOpsiAttribute(): array
    {
        return [
            'A' => $this->opsi_a,
            'B' => $this->opsi_b,
            'C' => $this->opsi_c,
            'D' => $this->opsi_d,
            'E' => $this->opsi_e,
        ];
    }
}