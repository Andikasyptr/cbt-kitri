@extends('layouts.app')
@section('title', $ujian->judul ?? 'Detail Ujian')
@section('content')

<a href="{{ route('admin.soal.index') }}" class="mb-4 inline-block text-sm text-blue-600 hover:underline">&larr; Kembali ke daftar soal</a>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
    <div class="card space-y-1 p-5 text-sm lg:col-span-2">
        <div class="mb-2 flex flex-wrap items-center gap-2">
            @include('guru.ujian._badge', ['u' => $ujian])
            <span class="text-slate-500">{{ $ujian->mapel->nama ?? '-' }}</span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Hanya lihat</span>
        </div>
        <div><span class="text-slate-500">Guru:</span> {{ $ujian->guru->name ?? '-' }}</div>
        <div><span class="text-slate-500">Kelas:</span> {{ $ujian->kelas->map->nama_lengkap->implode(', ') ?: '-' }}</div>
        <div>
            <span class="text-slate-500">Jadwal:</span>
            {{ $ujian->waktu_mulai?->translatedFormat('d M Y H:i') ?? '-' }} s/d {{ $ujian->waktu_selesai?->translatedFormat('d M Y H:i') ?? '-' }}
        </div>
        <div><span class="text-slate-500">Durasi:</span> {{ $ujian->durasi_menit ?? '-' }} menit &middot; {{ $ujian->acak_soal ? 'Soal diacak' : 'Soal berurutan' }}</div>
    </div>

    <div class="rounded-2xl bg-slate-900 p-5 text-center text-white shadow-sm">
        <div class="text-xs uppercase tracking-wider text-slate-400">Token Ujian</div>
        <div class="my-3 font-mono text-4xl font-bold tracking-[0.3em]">{{ $ujian->token ?? '-' }}</div>
        <div class="text-xs text-slate-400">{{ $ujian->soals->count() }} soal</div>
    </div>
</div>

<div class="space-y-4">
    @forelse ($ujian->soals as $i => $s)
        <div class="card p-5">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-sm font-semibold text-blue-700">Soal {{ $i + 1 }}</span>
                {{-- kunci --}}
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Kunci: {{ $s->kunci ?? '-' }}</span>
            </div>
            <p class="mb-4 whitespace-pre-line">{{ $s->pertanyaan }}</p>

            @if ($s->gambar_exists)
                <img src="{{ route('soal.gambar', $s) }}?v={{ $s->updated_at->timestamp }}" class="mb-4 max-h-72 rounded-lg border" alt="Gambar soal">
            @endif

            <div class="space-y-2">
                @foreach ($s->opsi as $huruf => $teks)
                    {{-- kunci --}}
                    @php $benar = $huruf === $s->kunci; @endphp
                    <div class="flex gap-3 rounded-lg border px-4 py-2.5 text-sm {{ $benar ? 'border-green-300 bg-green-50' : 'border-slate-200' }}">
                        <span class="font-bold {{ $benar ? 'text-green-700' : '' }}">{{ $huruf }}.</span>
                        <span class="whitespace-pre-line">{{ $teks }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card p-8 text-center text-slate-400">Guru belum menambahkan soal pada ujian ini.</div>
    @endforelse
</div>
@endsection