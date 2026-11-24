<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('kelas.jurusan');

        $awal  = now()->startOfDay();
        $akhir = now()->endOfDay();

        // Hanya ujian untuk kelas siswa ini yang jadwalnya mencakup hari ini
        $ujians = $user->kelas_id
            ? Ujian::untukKelas($user->kelas_id)
                ->where('aktif', true)
                ->where('waktu_mulai', '<=', $akhir)
                ->where('waktu_selesai', '>=', $awal)
                ->with('mapel')
                ->orderBy('waktu_mulai')
                ->get()
            : collect();

        $hasils = $user->hasilUjians()->get()->keyBy('ujian_id');

        return view('siswa.dashboard', compact('user', 'ujians', 'hasils'));
    }
}