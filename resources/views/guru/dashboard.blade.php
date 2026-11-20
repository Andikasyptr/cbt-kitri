@extends('layouts.app')
@section('title', 'Dashboard Guru')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @foreach ([['Total Ujian', $jumlahUjian], ['Sedang Berlangsung', $jumlahBerlangsung], ['Total Soal', $jumlahSoal]] as [$label, $angka])
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-slate-500">{{ $label }}</div>
            <div class="text-3xl font-bold mt-1">{{ $angka }}</div>
        </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow p-5">
    <div class="flex justify-between items-center mb-3">
        <h2 class="font-semibold">Ujian Terbaru</h2>
        <a href="{{ route('guru.ujian.create') }}" class="text-sm text-blue-600 hover:underline">+ Buat ujian</a>
    </div>
    @forelse ($ujianTerbaru as $u)
        <a href="{{ route('guru.ujian.show', $u) }}" class="flex justify-between py-2 border-t text-sm hover:bg-slate-50">
            <span>{{ $u->judul }} <span class="text-slate-400">({{ $u->mapel->nama ?? '-' }})</span></span>
            @include('guru.ujian._badge', ['u' => $u])
        </a>
    @empty
        <p class="text-sm text-slate-400">Belum ada ujian.</p>
    @endforelse
</div>
@endsection