<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->onlyInput('username');
        }

        $user = Auth::user();

        // Siswa yang diblokir (misalnya belum membayar SPP) tidak boleh masuk
        if ($user->role === 'siswa' && $user->diblokir) {
            $pesan = 'Akun Anda diblokir sementara.'
                . ($user->alasan_blokir ? ' Alasan: ' . $user->alasan_blokir . '.' : '')
                . ' Silakan hubungi bagian administrasi sekolah.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['username' => $pesan])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->route($this->homeRoute($user->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeRoute(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'guru'  => 'guru.dashboard',
            default => 'siswa.dashboard',
        };
    }
}