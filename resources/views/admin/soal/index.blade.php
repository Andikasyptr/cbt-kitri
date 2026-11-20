<div>
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
</div>
@extends('layouts.app')
@section('title', 'Daftar Soal')
@section('content')

<form method="GET" class="card mb-4 flex flex-col gap-2 p-4 sm:flex-row">
    <input name="q" value="{{ request('q') }}" placeholder="Cari judul ujian atau token" class="flex-1 px-3 py-2">
    <select name="guru_id" class="px-3 py-2">
        <option value="">Semua guru</option>
        @foreach ($gurus as $g)
            <option value="{{ $g->id }}" @selected(request('guru_id') == $g->id)>{{ $g->name }}</option>
        @endforeach
    </select>
    <select name="mapel_id" class="px-3 py-2">
        <option value="">Semua mapel</option>
        @foreach ($mapels as $m)
            <option value="{{ $m->id }}" @selected(request('mapel_id') == $m->id)>{{ $m->nama }}</option>
        @endforeach
    </select>
    <button class="btn bg-slate-800 text-white hover:bg-slate-900">Cari</button>
</form>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-600">
            <tr>
                <th class="p-3">Ujian</th>
                <th class="p-3">Guru</th>
                <th class="p-3">Kelas</th>
                <th class="p-3">Token</th>
                <th class="p-3">Soal</th>
                <th class="p-3">Status</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($ujians as $u)
            <tr class="border-t border-slate-100 align-top">
                <td class="p-3">
                    <div class="font-medium">{{ $u->judul ?? '-' }}</div>
                    <div class="text-xs text-slate-500">{{ $u->mapel->nama ?? '-' }} &middot; {{ $u->durasi_menit ?? '-' }} menit</div>
                </td>
                <td class="p-3">{{ $u->guru->name ?? '-' }}</td>
                <td class="p-3">{{ $u->kelas->map->nama_lengkap->implode(', ') ?: '-' }}</td>
                <td class="p-3 font-mono font-bold tracking-widest">{{ $u->token ?? '-' }}</td>
                <td class="p-3">{{ $u->soals_count ?: '-' }}</td>
                <td class="p-3">@include('guru.ujian._badge', ['u' => $u])</td>
                <td class="p-3 text-right">
                    <a href="{{ route('admin.soal.show', $u) }}" class="text-blue-600 hover:underline">Lihat soal</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-center text-slate-400">Belum ada ujian yang dibuat guru.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $ujians->links() }}</div>
@endsection