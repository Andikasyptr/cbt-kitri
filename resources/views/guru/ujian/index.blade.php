@extends('layouts.app')
@section('title', 'Daftar Ujian')
@section('content')

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        @foreach ($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<a href="{{ route('guru.ujian.create') }}" class="inline-block mb-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm">+ Buat Ujian</a>

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="p-3">Ujian</th><th class="p-3">Kelas</th><th class="p-3">Jadwal</th>
                <th class="p-3">Token</th><th class="p-3">Soal</th><th class="p-3">Status</th><th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($ujians as $u)
            <tr class="border-t align-top">
                <td class="p-3">
                    <div class="font-medium">{{ $u->judul }}</div>
                    <div class="text-xs text-slate-500">{{ $u->mapel->nama ?? '-' }} &middot; {{ $u->durasi_menit }} menit</div>
                </td>
                <td class="p-3">{{ $u->kelas->map->nama_lengkap->implode(', ') }}</td>
                <td class="p-3 text-xs">
                    {{ $u->waktu_mulai->translatedFormat('d M Y H:i') }}<br>s/d {{ $u->waktu_selesai->translatedFormat('d M Y H:i') }}
                </td>
                <td class="p-3 font-mono font-bold tracking-widest">{{ $u->token }}</td>
                <td class="p-3">{{ $u->soals_count }}</td>
                <td class="p-3">@include('guru.ujian._badge', ['u' => $u])</td>
                <td class="p-3 text-right space-x-3 whitespace-nowrap">
                    <a href="{{ route('guru.ujian.show', $u) }}" class="text-blue-600 hover:underline">Kelola</a>
                    <a href="{{ route('guru.nilai.index', $u) }}" class="text-green-600 hover:underline">Nilai ({{ $u->hasils_count }})</a>
                    <a href="{{ route('guru.ujian.edit', $u) }}" class="text-slate-600 hover:underline">Edit</a>
                    <form action="{{ route('guru.ujian.destroy', $u) }}" method="POST" class="inline"
                          onsubmit="return confirm('Hapus ujian &quot;{{ addslashes($u->judul) }}&quot;?{{ $u->hasils_count ? ' Ujian ini sudah dikerjakan '.$u->hasils_count.' siswa, nilainya ikut terhapus.' : '' }} Soal di dalamnya juga ikut terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-center text-slate-400">Belum ada ujian.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection