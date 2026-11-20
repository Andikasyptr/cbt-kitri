<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalGambar extends Model
{
    protected $table = 'soal_gambars';
    protected $guarded = [];

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}