@extends('layouts.app')
@section('title', $ujian->judul)
@section('content')

{{-- Ringkasan + token --}}
<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow p-5 text-sm space-y-1">
        <div class="flex items-center gap-2 mb-2">
            @include('guru.ujian._badge', ['u' => $ujian])
            <span class="text-slate-500">{{ $ujian->mapel->nama ?? '-' }}</span>
        </div>
        <div><span class="text-slate-500">Kelas:</span> {{ $ujian->kelas->map->nama_lengkap->implode(', ') }}</div>
        <div><span class="text-slate-500">Jadwal:</span> {{ $ujian->waktu_mulai->translatedFormat('d M Y H:i') }} s/d {{ $ujian->waktu_selesai->translatedFormat('d M Y H:i') }}</div>
        <div><span class="text-slate-500">Durasi:</span> {{ $ujian->durasi_menit }} menit &middot; {{ $ujian->acak_soal ? 'Soal diacak' : 'Soal berurutan' }}</div>
        <div class="pt-3 flex flex-wrap gap-2">
            <a href="{{ route('guru.ujian.edit', $ujian) }}" class="border rounded-lg px-3 py-1.5 hover:bg-slate-50">Edit</a>
            <a href="{{ route('guru.nilai.index', $ujian) }}" class="bg-green-600 text-white rounded-lg px-3 py-1.5 hover:bg-green-700">Lihat Nilai</a>
            <form action="{{ route('guru.ujian.toggle', $ujian) }}" method="POST">
                @csrf
                <button class="border rounded-lg px-3 py-1.5 hover:bg-slate-50">{{ $ujian->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
        </div>
    </div>

    <div class="bg-slate-900 text-white rounded-xl shadow p-5 text-center">
        <div class="text-xs uppercase tracking-wider text-slate-400">Token Ujian</div>
        <div class="text-4xl font-mono font-bold tracking-[0.3em] my-3">{{ $ujian->token }}</div>
        <form action="{{ route('guru.ujian.token', $ujian) }}" method="POST"
              onsubmit="return confirm('Buat token baru? Token lama tidak berlaku lagi.')">
            @csrf
            <button class="text-xs text-blue-300 hover:underline">Buat token baru</button>
        </form>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- Import Excel --}}
    <div class="bg-white rounded-xl shadow p-5 space-y-3">
        <h2 class="font-semibold">Import Soal dari Excel</h2>
        <p class="text-sm text-slate-500">Kolom: Pertanyaan, Opsi A–E, Kunci (A–E). Baris pertama adalah header.</p>
        <a href="{{ route('guru.soal.template', $ujian) }}" class="inline-block text-sm text-blue-600 hover:underline">⬇ Download template Excel</a>
        <form action="{{ route('guru.soal.import', $ujian) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls" required class="block w-full text-sm border rounded-lg p-2">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="hapus_lama" value="1"> Hapus semua soal lama sebelum import
            </label>
            <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm">Import</button>
        </form>
    </div>

    {{-- Soal manual --}}
    <form action="{{ route('guru.soal.store', $ujian) }}" method="POST" class="bg-white rounded-xl shadow p-5 space-y-3">
        @csrf
        <h2 class="font-semibold">Tambah Soal Manual</h2>
        <textarea name="pertanyaan" rows="3" placeholder="Pertanyaan" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('pertanyaan') }}</textarea>
        @foreach (['a', 'b', 'c', 'd', 'e'] as $o)
            <div class="flex items-center gap-2">
                <span class="w-6 font-semibold uppercase">{{ $o }}.</span>
                <input name="opsi_{{ $o }}" value="{{ old('opsi_'.$o) }}" required class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            </div>
        @endforeach
        <div class="flex items-center gap-2">
            <label class="text-sm">Kunci jawaban:</label>
            <select name="kunci" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                @foreach (['A', 'B', 'C', 'D', 'E'] as $k)
                    <option @selected(old('kunci') == $k)>{{ $k }}</option>
                @endforeach
            </select>
            <button class="ml-auto bg-green-600 hover:bg-green-700 text-white rounded-lg px-4 py-2 text-sm">Tambah</button>
        </div>
    </form>
</div>

{{-- Daftar soal --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-4 border-b font-semibold">Daftar Soal ({{ $ujian->soals->count() }})</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left">
            <tr><th class="p-3 w-12">No</th><th class="p-3">Pertanyaan</th><th class="p-3 w-20">Kunci</th><th class="p-3 w-32"></th></tr>
        </thead>
        <tbody>
        @forelse ($ujian->soals as $i => $s)
            <tr class="border-t align-top">
                <td class="p-3">{{ $i + 1 }}</td>
                <td class="p-3 whitespace-pre-line">{{ Str::limit($s->pertanyaan, 150) }}</td>
                <td class="p-3 font-bold">{{ $s->kunci }}</td>
                <td class="p-3 text-right space-x-3 whitespace-nowrap">
                    <a href="{{ route('guru.soal.edit', $s) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('guru.soal.destroy', $s) }}" method="POST" class="inline"
                          onsubmit="return confirm('Hapus soal ini?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada soal.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection