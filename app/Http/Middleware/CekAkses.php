<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekAkses
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'siswa' && $user->diblokir) {
            $pesan = 'Akun Anda diblokir sementara.'
                . ($user->alasan_blokir ? ' Alasan: ' . $user->alasan_blokir . '.' : '')
                . ' Silakan hubungi bagian administrasi sekolah.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Permintaan AJAX (simpan jawaban ujian) -> arahkan browser ke login
            if ($request->expectsJson()) {
                return response()->json(['selesai' => true, 'redirect' => route('login')], 403);
            }

            return redirect()->route('login')->withErrors(['username' => $pesan]);
        }

        return $next($request);
    }
}