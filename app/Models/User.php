<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed', // otomatis di-hash saat disimpan
        ];
    }

    // ---------- Relasi ----------
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    // Ujian yang dibuat guru
    public function ujians(): HasMany
    {
        return $this->hasMany(Ujian::class, 'guru_id');
    }

    // Riwayat ujian yang dikerjakan siswa
    public function hasilUjians(): HasMany
    {
        return $this->hasMany(HasilUjian::class, 'user_id');
    }

    // ---------- Helper role ----------
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    // ---------- Scope ----------
    public function scopeGuru($query)
    {
        return $query->where('role', 'guru');
    }

    public function scopeSiswa($query)
    {
        return $query->where('role', 'siswa');
    }
}