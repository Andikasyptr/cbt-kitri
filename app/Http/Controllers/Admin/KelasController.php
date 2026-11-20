<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Jurusan, Kelas};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index()
    {
        return view('admin.kelas.index', [
            'kelasList' => Kelas::with('jurusan')->withCount('siswa')
                ->orderBy('tingkat')->orderBy('jurusan_id')->orderBy('kode')->get(),
            'jurusans' => Jurusan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tingkat'    => ['required', Rule::in([10, 11, 12])],
            'jurusan_id' => 'required|exists:jurusans,id',
            'kode'       => [
                'required', 'string', 'max:10',
                Rule::unique('kelas')->where(fn ($q) => $q
                    ->where('tingkat', $request->tingkat)
                    ->where('jurusan_id', $request->jurusan_id)),
            ],
        ], ['kode.unique' => 'Kelas dengan tingkat, jurusan, dan kode tersebut sudah ada.']);

        Kelas::create($data);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return back()->with('success', 'Kelas dihapus.');
    }
}