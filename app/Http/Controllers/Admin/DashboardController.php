<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Kelas, Mapel, User};

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'jumlahGuru'  => User::where('role', 'guru')->count(),
            'jumlahSiswa' => User::where('role', 'siswa')->count(),
            'jumlahKelas' => Kelas::count(),
            'jumlahMapel' => Mapel::count(),
        ]);
    }
}