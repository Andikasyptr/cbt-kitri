<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas'; // tanpa ini Laravel menebak "kelas" -> "kelases"
    protected $guarded = [];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(User::class, 'kelas_id');
    }

    public function ujians(): BelongsToMany
    {
        return $this->belongsToMany(Ujian::class, 'kelas_ujian', 'kelas_id', 'ujian_id');
    }

    // Contoh hasil: "XII TKJ A"
    public function getNamaLengkapAttribute(): string
    {
        $romawi = [10 => 'X', 11 => 'XI', 12 => 'XII'][$this->tingkat] ?? $this->tingkat;

        return trim($romawi . ' ' . ($this->jurusan->singkatan ?? '') . ' ' . $this->kode);
    }
}