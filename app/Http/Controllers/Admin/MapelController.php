<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        return view('admin.mapel.index', ['mapels' => Mapel::orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        Mapel::create($request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'nullable|string|max:20',
        ]));

        return back()->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();

        return back()->with('success', 'Mata pelajaran dihapus.');
    }
}