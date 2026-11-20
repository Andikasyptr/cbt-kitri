@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
@php
    $stat = [
        ['Guru', $jumlahGuru, 'bg-blue-100 text-blue-600', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['Siswa', $jumlahSiswa, 'bg-emerald-100 text-emerald-600', 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        ['Kelas', $jumlahKelas, 'bg-amber-100 text-amber-600', 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['Mata Pelajaran', $jumlahMapel, 'bg-violet-100 text-violet-600', 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
    ];
@endphp

{{-- Banner sambutan --}}
<div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 p-5 text-white shadow-lg shadow-blue-900/10 sm:p-7">
    <div class="pointer-events-none absolute -right-10 -top-10 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
    <div class="relative">
        <p class="text-sm text-blue-100">{{ now()->translatedFormat('l, d F Y') }}</p>
        <h2 class="mt-1 text-xl font-bold sm:text-2xl">Halo, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 max-w-xl text-sm text-blue-100">Kelola data sekolah: jurusan, kelas, mata pelajaran, serta akun guru dan siswa.</p>
    </div>
</div>

{{-- Statistik --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    @foreach ($stat as [$label, $angka, $warna, $path])
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div class="text-xs font-medium text-slate-500 sm:text-sm">{{ $label }}</div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl sm:h-10 sm:w-10 {{ $warna }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                </div>
            </div>
            <div class="mt-3 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ number_format($angka) }}</div>
        </div>
    @endforeach
</div>

{{-- Aksi cepat --}}
<div class="card p-4 sm:p-6">
    <h3 class="mb-4 font-semibold text-slate-900">Aksi Cepat</h3>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Tambah Jurusan', 'admin.jurusan.index'],
            ['Tambah Kelas', 'admin.kelas.index'],
            ['Tambah Mapel', 'admin.mapel.index'],
            ['Daftarkan Siswa', 'admin.siswa.index'],
        ] as [$teks, $rute])
            <a href="{{ route($rute) }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                {{ $teks }} <span aria-hidden="true">&rarr;</span>
            </a>
        @endforeach
    </div>
</div>
@endsection