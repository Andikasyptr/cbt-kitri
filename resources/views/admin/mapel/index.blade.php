@extends('layouts.app')
@section('title', 'Mata Pelajaran')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form action="{{ route('admin.mapel.store') }}" method="POST" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-semibold text-slate-900">Tambah Mata Pelajaran</h2>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Mata Pelajaran</label>
            <input name="nama" value="{{ old('nama') }}" placeholder="Pendidikan Agama Islam" required class="w-full px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Kode (opsional)</label>
            <input name="kode" value="{{ old('kode') }}" placeholder="PAI" class="w-full px-3 py-2">
        </div>
        <button class="btn-primary w-full">Simpan</button>
    </form>

    <div class="card overflow-x-auto lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr><th class="p-3">Nama</th><th class="p-3">Kode</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
            @forelse ($mapels as $m)
                <tr class="border-t border-slate-100">
                    <td class="p-3">{{ $m->nama }}</td>
                    <td class="p-3">{{ $m->kode ?? '-' }}</td>
                    <td class="p-3 text-right">
                        <form action="{{ route('admin.mapel.destroy', $m) }}" method="POST"
                              onsubmit="return confirm('Hapus mata pelajaran ini? Ujian yang memakainya ikut terhapus.')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="p-6 text-center text-slate-400">Belum ada mata pelajaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection