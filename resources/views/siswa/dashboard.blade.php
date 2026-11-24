@extends('layouts.app')
@section('title', 'Ujian Saya')

@section('content')
<div class="space-y-6">
    {{-- Banner + Input Token --}}
    <div id="panel-token" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 p-6 text-white shadow-lg shadow-blue-950/15 sm:p-8">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 left-1/3 h-48 w-48 rounded-full bg-blue-400/10 blur-2xl"></div>

        <div class="relative grid gap-6 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-blue-100 ring-1 ring-inset ring-white/15">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">
                    Halo, {{ $user->name }} 👋
                </h1>
                <p class="mt-1 text-sm text-blue-100/90">
                    Lihat ujian yang dijadwalkan hari ini, lalu masukkan token dari pengawas untuk mulai mengerjakan.
                </p>
                <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-white ring-1 ring-inset ring-white/20">
                    <svg class="h-4 w-4 text-blue-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                    </svg>
                    Kelas {{ $user->kelas?->nama_lengkap ?? 'Belum Diatur' }}
                </div>
            </div>

            <div class="lg:col-span-5">
                <form action="{{ route('siswa.token') }}" method="POST" class="overflow-hidden rounded-xl border border-white/10 bg-white p-5 text-slate-800 shadow-xl">
                    @csrf
                    <div class="mb-2 flex items-center justify-between">
                        <label for="input-token" class="text-xs font-bold uppercase tracking-wider text-slate-700">
                            Token Ujian
                        </label>
                        <span class="text-[11px] font-medium text-slate-400">6 Karakter</span>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input id="input-token"
                               name="token"
                               value="{{ old('token') }}"
                               maxlength="10"
                               autocomplete="off"
                               autocapitalize="characters"
                               required
                               placeholder="K7M2QX"
                               class="w-full min-w-0 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-center font-mono text-base font-bold uppercase tracking-[0.15em] text-slate-900 placeholder-slate-300 transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 sm:flex-1">
                        <button type="submit" class="inline-flex w-full shrink-0 items-center justify-center gap-1.5 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 sm:w-auto">
                            Mulai
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>

                    @error('token')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-2.5 text-xs text-slate-500">
                        Token diberikan oleh guru atau pengawas ruangan.
                    </p>
                </form>
            </div>
        </div>
    </div>

    {{-- Peringatan Jika Kelas Belum Diatur --}}
    @unless ($user->kelas)
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div>
                <p class="font-semibold">Rombongan Belajar Belum Terdaftar</p>
                <p class="mt-0.5 text-xs text-amber-700">Akun Anda belum memiliki kelas. Hubungi admin atau operator sekolah agar daftar ujian dapat ditampilkan.</p>
            </div>
        </div>
    @endunless

    {{-- Header Daftar Ujian --}}
    <div class="flex flex-col justify-between gap-2 border-b border-slate-200 pb-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Ujian Hari Ini &middot; {{ now()->translatedFormat('l, d F Y') }}
            </h2>
            <p class="text-xs text-slate-500">Kelas {{ $user->kelas?->nama_lengkap ?? '-' }}. Pastikan jam pelaksanaan sesuai sebelum memulai.</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
            Total: {{ $ujians->count() }} Ujian
        </span>
    </div>

    {{-- Grid Kartu Ujian --}}
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($ujians as $u)
            @php
                $h = $hasils->get($u->id);
                $selesai = $h && $h->selesai_at;
            @endphp

            <div class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md">
                {{-- Bagian Atas Kartu --}}
                <div class="p-5 pb-4">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="inline-block rounded bg-blue-50 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-blue-700">
                                {{ $u->mapel->nama ?? 'Mata Pelajaran' }}
                            </span>
                            <h3 class="mt-1.5 line-clamp-2 text-base font-bold leading-snug text-slate-900">
                                {{ $u->judul ?? '-' }}
                            </h3>
                        </div>

                        @if ($selesai)
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Selesai
                            </span>
                        @elseif ($h)
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>
                                Dikerjakan
                            </span>
                        @else
                            <div class="shrink-0">
                                @include('guru.ujian._badge', ['u' => $u])
                            </div>
                        @endif
                    </div>

                    {{-- Detail Jadwal & Durasi --}}
                    <dl class="mt-4 space-y-2 border-t border-slate-100 pt-3.5 text-xs text-slate-600">
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-1.5 text-slate-500">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Mulai
                            </dt>
                            <dd class="font-semibold text-slate-800">{{ $u->waktu_mulai?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-1.5 text-slate-500">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Selesai
                            </dt>
                            <dd class="font-semibold text-slate-800">{{ $u->waktu_selesai?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-1.5 text-slate-500">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Durasi
                            </dt>
                            <dd class="font-semibold text-slate-800">{{ $u->durasi_menit ?? '-' }} menit</dd>
                        </div>
                    </dl>
                </div>

                {{-- Footer Aksi Kartu --}}
                <div class="mt-auto border-t border-slate-100 bg-slate-50/70 p-4">
                    @if ($selesai)
                        <a href="{{ route('siswa.ujian.hasil', $h) }}"
                           class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            Lihat Hasil Ujian
                        </a>
                    @elseif ($h)
                        <a href="{{ route('siswa.ujian.kerjakan', $h) }}"
                           class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-amber-600">
                            Lanjutkan Mengerjakan
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @else
                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-center text-xs font-medium text-slate-500">
                            Masukkan token dari pengawas untuk memulai
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <p class="mt-2 text-sm font-semibold text-slate-900">Tidak ada ujian hari ini</p>
                <p class="mt-1 text-xs text-slate-500">Belum ada ujian yang dijadwalkan untuk kelas Anda hari ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection