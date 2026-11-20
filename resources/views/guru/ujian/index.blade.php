@extends('layouts.app')
@section('title', 'Daftar Ujian')
@section('content')
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
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-center text-slate-400">Belum ada ujian.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection