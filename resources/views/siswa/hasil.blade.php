@extends('layouts.app')
@section('title', 'Hasil Ujian')
@section('content')
<div class="bg-white rounded-xl shadow p-8 max-w-lg text-center space-y-4">
    <div class="text-5xl">✅</div>
    <h2 class="text-xl font-bold">Ujian berhasil dikumpulkan</h2>
    <div class="text-slate-500">{{ $hasil->ujian->judul }} &middot; {{ $hasil->ujian->mapel->nama ?? '-' }}</div>

    {{-- Hapus blok ini jika sekolah tidak ingin nilai langsung terlihat oleh siswa --}}
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="bg-slate-50 rounded-lg p-3"><div class="text-slate-500">Jawaban benar</div><div class="font-bold text-lg">{{ $hasil->benar }} / {{ $total }}</div></div>
        <div class="bg-slate-50 rounded-lg p-3"><div class="text-slate-500">Nilai</div><div class="font-bold text-lg">{{ $hasil->nilai }}</div></div>
    </div>

    @if ($hasil->pelanggaran > 0)
        <p class="text-sm text-red-600">Tercatat {{ $hasil->pelanggaran }} pelanggaran selama ujian.</p>
    @endif

    <a href="{{ route('siswa.dashboard') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-5 py-2">Kembali ke Dashboard</a>
</div>
@endsection