<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('kelas.jurusan');

        // Hanya ujian yang ditujukan untuk kelas siswa ini
        $ujians = $user->kelas_id
            ? Ujian::untukKelas($user->kelas_id)
                ->where('aktif', true)
                ->with('mapel')
                ->orderByDesc('waktu_mulai')
                ->get()
            : collect();

        $hasils = $user->hasilUjians()->get()->keyBy('ujian_id');

        return view('siswa.dashboard', compact('user', 'ujians', 'hasils'));
    }
}