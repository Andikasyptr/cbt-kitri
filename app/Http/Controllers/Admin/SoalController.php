<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Mapel, Ujian, User};
use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $ujians = Ujian::with(['guru', 'mapel', 'kelas.jurusan'])
            ->withCount('soals')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('judul', 'like', "%$s%")
                ->orWhere('token', 'like', '%' . strtoupper($s) . '%')))
            ->when($request->guru_id, fn ($q, $g) => $q->where('guru_id', $g))
            ->when($request->mapel_id, fn ($q, $m) => $q->where('mapel_id', $m))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.soal.index', [
            'ujians' => $ujians,
            'gurus'  => User::guru()->orderBy('name')->get(['id', 'name']),
            'mapels' => Mapel::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function show(Ujian $ujian)
    {
        $ujian->load([
            'guru', 'mapel', 'kelas.jurusan',
            'soals' => fn ($q) => $q->withExists('gambar'),
        ]);

        return view('admin.soal.show', compact('ujian'));
    }
}