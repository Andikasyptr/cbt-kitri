<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB; // <-- Tambahkan baris ini di sini

class HasilUjian extends Model
{
    protected $table = 'hasil_ujians';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mulai_at'    => 'datetime',
            'selesai_at'  => 'datetime',
            'nilai'       => 'decimal:2',
            'benar'       => 'integer',
            'pelanggaran' => 'integer',
        ];
    }

    // ---------- Relasi ----------
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(Jawaban::class);
    }

    // ---------- Helper ----------
    public function sudahSelesai(): bool
    {
        return $this->selesai_at !== null;
    }

    // Batas akhir pengerjaan siswa ini: mana yang lebih awal antara
    // (waktu mulai + durasi) dan (waktu selesai ujian yang dijadwalkan).
    public function batasWaktu()
    {
        $batasDurasi = $this->mulai_at->copy()->addMinutes($this->ujian->durasi_menit);

        return $batasDurasi->lt($this->ujian->waktu_selesai)
            ? $batasDurasi
            : $this->ujian->waktu_selesai;
    }

    // Sisa waktu dalam detik (minimal 0)
    public function sisaDetik(): int
    {
        return max(0, (int) now()->diffInSeconds($this->batasWaktu(), false));
    }

    // Hitung nilai dan tutup ujian. Aman dipanggil berulang.
    public function selesaikan(): void
    {
        if ($this->selesai_at) {
            return;
        }

        $total = $this->ujian->soals()->count();

        $benar = DB::table('jawabans')
            ->join('soals', 'soals.id', '=', 'jawabans.soal_id')
            ->where('jawabans.hasil_ujian_id', $this->id)
            ->whereColumn('jawabans.jawaban', 'soals.kunci')
            ->count();

        $this->update([
            'selesai_at' => now(),
            'benar'      => $benar,
            'nilai'      => $total > 0 ? round($benar / $total * 100, 2) : 0,
        ]);
    }
}