<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        return view('admin.jurusan.index', [
            'jurusans' => Jurusan::withCount('kelas')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:100',
            'singkatan' => 'required|string|max:10|unique:jurusans,singkatan',
        ]);

        Jurusan::create($data);

        return back()->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();

        return back()->with('success', 'Jurusan dihapus.');
    }
}