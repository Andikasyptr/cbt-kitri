<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankSoal extends Model
{
    protected $table = 'bank_soals';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['ada_gambar' => 'boolean'];
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

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

    // Sidik jari isi soal (huruf kecil, spasi dirapikan) untuk mendeteksi soal yang sama
    public static function hitung(?string $tanya, ?string $a, ?string $b, ?string $c, ?string $d, ?string $e, ?string $kunci): string
    {
        $n = fn ($s) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $s)));

        return sha1(implode('|', [$n($tanya), $n($a), $n($b), $n($c), $n($d), $n($e), strtoupper((string) $kunci)]));
    }

    // Salin semua soal sebuah ujian ke bank guru pemiliknya. Mengembalikan jumlah soal baru.
    public static function arsipkanUjian(Ujian $ujian): int
    {
        $baru = 0;

        foreach ($ujian->soals()->with('gambar')->get() as $s) {
            $hash = self::hitung($s->pertanyaan, $s->opsi_a, $s->opsi_b, $s->opsi_c, $s->opsi_d, $s->opsi_e, $s->kunci);

            $item = self::firstOrCreate(
                ['guru_id' => $ujian->guru_id, 'hash' => $hash],
                [
                    'mapel_id'    => $ujian->mapel_id,
                    'sumber'      => $ujian->judul,
                    'pertanyaan'  => $s->pertanyaan,
                    'opsi_a'      => $s->opsi_a,
                    'opsi_b'      => $s->opsi_b,
                    'opsi_c'      => $s->opsi_c,
                    'opsi_d'      => $s->opsi_d,
                    'opsi_e'      => $s->opsi_e,
                    'kunci'       => $s->kunci,
                    'ada_gambar'  => (bool) $s->gambar,
                    'gambar_mime' => $s->gambar?->mime,
                    'gambar_data' => $s->gambar?->data,
                ]
            );

            if ($item->wasRecentlyCreated) {
                $baru++;
            }
        }

        return $baru;
    }
}