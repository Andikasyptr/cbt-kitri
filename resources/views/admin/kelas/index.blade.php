@extends('layouts.app')
@section('title', 'Data Kelas')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form action="{{ route('admin.kelas.store') }}" method="POST" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-semibold text-slate-900">Tambah Kelas</h2>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Tingkat</label>
            <select name="tingkat" required class="w-full px-3 py-2">
                @foreach ([10 => 'X (10)', 11 => 'XI (11)', 12 => 'XII (12)'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('tingkat') == $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Jurusan</label>
            <select name="jurusan_id" required class="w-full px-3 py-2">
                <option value="">-- pilih jurusan --</option>
                @foreach ($jurusans as $j)
                    <option value="{{ $j->id }}" @selected(old('jurusan_id') == $j->id)>{{ $j->nama }} ({{ $j->singkatan }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Kode Kelas</label>
            <input name="kode" value="{{ old('kode') }}" placeholder="A / B / 1 / 2" required class="w-full px-3 py-2">
        </div>
        <button class="btn-primary w-full">Simpan</button>
    </form>

    <div class="card overflow-x-auto lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="p-3">Kelas</th>
                    <th class="p-3">Jurusan</th>
                    <th class="p-3">Kode</th>
                    <th class="p-3">Jml Siswa</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($kelasList as $k)
                <tr class="border-t border-slate-100">
                    <td class="p-3 font-medium">{{ $k->nama_lengkap }}</td>
                    <td class="p-3">{{ $k->jurusan->nama ?? '-' }}</td>
                    <td class="p-3">{{ $k->kode ?: '-' }}</td>
                    <td class="p-3">{{ $k->siswa_count ?: '-' }}</td>
                    <td class="p-3 text-right">
                        <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST"
                              onsubmit="return confirm('Hapus kelas ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada kelas. Tambahkan jurusan dulu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection