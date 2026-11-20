<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jawaban extends Model
{
    protected $table = 'jawabans';
    protected $guarded = [];

    public function hasilUjian(): BelongsTo
    {
        return $this->belongsTo(HasilUjian::class);
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}