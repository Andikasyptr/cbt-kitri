<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Soal;

class DashboardController extends Controller
{
    public function index()
    {
        $guru = auth()->user();

        return view('guru.dashboard', [
            'jumlahUjian'       => $guru->ujians()->count(),
            'jumlahBerlangsung' => $guru->ujians()->berlangsung()->count(),
            'jumlahSoal'        => Soal::whereHas('ujian', fn ($q) => $q->where('guru_id', $guru->id))->count(),
            'ujianTerbaru'      => $guru->ujians()->with('mapel')->latest()->take(5)->get(),
        ]);
    }
}