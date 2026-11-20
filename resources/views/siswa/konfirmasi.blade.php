@extends('layouts.app')
@section('title', 'Konfirmasi Ujian')
@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl space-y-5">
    <div>
        <div class="text-sm text-slate-500">{{ $ujian->mapel->nama ?? '-' }}</div>
        <h2 class="text-xl font-bold">{{ $ujian->judul }}</h2>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="bg-slate-50 rounded-lg p-3"><div class="text-slate-500">Jumlah soal</div><div class="font-bold text-lg">{{ $ujian->soals_count }}</div></div>
        <div class="bg-slate-50 rounded-lg p-3"><div class="text-slate-500">Durasi</div><div class="font-bold text-lg">{{ $ujian->durasi_menit }} menit</div></div>
    </div>

    <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm space-y-1">
        <div class="font-semibold text-yellow-800 mb-1">Baca aturan ujian:</div>
        <ul class="list-disc pl-5 text-yellow-900 space-y-1">
            <li>Ujian berjalan dalam mode <b>layar penuh</b>. Jangan keluar dari layar penuh.</li>
            <li>Dilarang berpindah tab, membuka aplikasi lain, atau menekan tombol Esc.</li>
            <li>Setiap pelanggaran dicatat dan dilihat guru. Pada pelanggaran ke-{{ $batas }}, ujian <b>otomatis dikumpulkan</b>.</li>
            <li>Jawaban tersimpan otomatis. Waktu terus berjalan walaupun halaman ditutup.</li>
            <li>Ujian hanya bisa dikerjakan <b>satu kali</b>.</li>
        </ul>
    </div>

    <form action="{{ route('siswa.ujian.mulai', $ujian) }}" method="POST" class="flex items-center gap-3">
        @csrf
        <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-2.5 font-medium">Saya Siap, Mulai Ujian</button>
        <a href="{{ route('siswa.dashboard') }}" class="text-sm text-slate-500 hover:underline">Batal</a>
    </form>
</div>
@endsection