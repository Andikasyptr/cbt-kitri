<?php

namespace App\Http\Controllers;

use App\Models\{HasilUjian, Soal};

class GambarSoalController extends Controller
{
    public function show(Soal $soal)
    {
        $user = auth()->user();
        $ujian = $soal->ujian;

        $boleh = match ($user->role) {
            'admin' => true,
            'guru'  => (int) $ujian->guru_id === (int) $user->id,
            // siswa hanya jika sudah memulai ujian ini
            default => HasilUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->exists(),
        };
        abort_unless($boleh, 403);

        $g = $soal->gambar;
        abort_if(! $g, 404);

        return response(base64_decode($g->data), 200, [
            'Content-Type'           => $g->mime ?: 'image/jpeg',
            'Cache-Control'          => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}