@extends('layouts.app')
@section('title', 'Data Jurusan')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form action="{{ route('admin.jurusan.store') }}" method="POST" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-semibold text-slate-900">Tambah Jurusan</h2>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Jurusan</label>
            <input name="nama" value="{{ old('nama') }}" placeholder="Teknik Komputer Jaringan" required class="w-full px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Singkatan</label>
            <input name="singkatan" value="{{ old('singkatan') }}" placeholder="TKJ" required class="w-full px-3 py-2">
        </div>
        <button class="btn-primary w-full">Simpan</button>
    </form>

    <div class="card overflow-x-auto lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr><th class="p-3">Nama</th><th class="p-3">Singkatan</th><th class="p-3">Jml Kelas</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
            @forelse ($jurusans as $j)
                <tr class="border-t border-slate-100">
                    <td class="p-3">{{ $j->nama }}</td>
                    <td class="p-3 font-medium">{{ $j->singkatan }}</td>
                    <td class="p-3">{{ $j->kelas_count }}</td>
                    <td class="p-3 text-right">
                        <form action="{{ route('admin.jurusan.destroy', $j) }}" method="POST"
                              onsubmit="return confirm('Hapus jurusan ini beserta kelasnya?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada jurusan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection