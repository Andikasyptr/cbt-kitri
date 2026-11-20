<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ujian extends Model
{
    protected $table = 'ujians';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'waktu_mulai'   => 'datetime',
            'waktu_selesai' => 'datetime',
            'acak_soal'     => 'boolean',
            'aktif'         => 'boolean',
            'durasi_menit'  => 'integer',
        ];
    }

    // ---------- Relasi ----------
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_ujian', 'ujian_id', 'kelas_id');
    }

    public function soals(): HasMany
    {
        return $this->hasMany(Soal::class);
    }

    public function hasils(): HasMany
    {
        return $this->hasMany(HasilUjian::class);
    }

    // ---------- Scope ----------
    // Ujian yang diizinkan untuk kelas tertentu
    public function scopeUntukKelas($query, $kelasId)
    {
        return $query->whereHas('kelas', fn ($q) => $q->where('kelas.id', $kelasId));
    }

    // Ujian aktif dan sedang dalam rentang waktu
    public function scopeBerlangsung($query)
    {
        return $query->where('aktif', true)
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now());
    }

    // ---------- Helper ----------
    public function sedangBerlangsung(): bool
    {
        return $this->aktif
            && $this->waktu_mulai
            && $this->waktu_selesai
            && now()->between($this->waktu_mulai, $this->waktu_selesai);
    }

    // Label status untuk tampilan: Nonaktif / Belum dimulai / Berlangsung / Selesai
    public function getStatusAttribute(): string
    {
        if (! $this->aktif) {
            return 'Nonaktif';
        }
        if ($this->waktu_mulai && now()->lt($this->waktu_mulai)) {
            return 'Belum dimulai';
        }
        if ($this->waktu_selesai && now()->gt($this->waktu_selesai)) {
            return 'Selesai';
        }

        return 'Berlangsung';
    }

    // Token acak 6 karakter huruf besar + angka (tanpa karakter membingungkan: 0, O, 1, I)
    public static function buatToken(): string
    {
        $karakter = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $token = '';
            for ($i = 0; $i < 6; $i++) {
                $token .= $karakter[random_int(0, strlen($karakter) - 1)];
            }
        } while (static::where('token', $token)->exists());

        return $token;
    }
}