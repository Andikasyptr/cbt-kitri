@extends('layouts.app')
@section('title', $ujian->judul)
@section('content')

@php
    $field = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500';
@endphp

{{-- Ringkasan + token --}}
<div class="mb-6 grid gap-6 lg:grid-cols-3">
    <div class="card space-y-1 p-5 text-sm lg:col-span-2">
        <div class="mb-2 flex items-center gap-2">
            @include('guru.ujian._badge', ['u' => $ujian])
            <span class="text-slate-500">{{ $ujian->mapel->nama ?? '-' }}</span>
        </div>
        <div><span class="text-slate-500">Kelas:</span> {{ $ujian->kelas->map->nama_lengkap->implode(', ') }}</div>
        <div><span class="text-slate-500">Jadwal:</span> {{ $ujian->waktu_mulai->translatedFormat('d M Y H:i') }} s/d {{ $ujian->waktu_selesai->translatedFormat('d M Y H:i') }}</div>
        <div><span class="text-slate-500">Durasi:</span> {{ $ujian->durasi_menit }} menit &middot; {{ $ujian->acak_soal ? 'Soal diacak' : 'Soal berurutan' }}</div>
        <div class="flex flex-wrap gap-2 pt-3">
            <a href="{{ route('guru.ujian.edit', $ujian) }}" class="btn-ghost">Edit</a>
            <a href="{{ route('guru.nilai.index', $ujian) }}" class="btn-success">Lihat Nilai</a>
            <form action="{{ route('guru.ujian.toggle', $ujian) }}" method="POST">
                @csrf
                <button class="btn-ghost">{{ $ujian->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900 p-5 text-center text-white shadow-sm">
        <div class="text-xs uppercase tracking-wider text-slate-400">Token Ujian</div>
        <div class="my-3 font-mono text-4xl font-bold tracking-[0.3em]">{{ $ujian->token }}</div>
        <form action="{{ route('guru.ujian.token', $ujian) }}" method="POST"
              onsubmit="return confirm('Buat token baru? Token lama tidak berlaku lagi.')">
            @csrf
            <button class="text-xs text-blue-300 hover:underline">Buat token baru</button>
        </form>
    </div>
</div>

{{-- Pesan sukses & error validasi --}}
@if (session('success'))
    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        <ul class="list-inside list-disc">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-6 grid gap-6 lg:grid-cols-2">
    {{-- Import Excel --}}
    <div class="card h-fit space-y-3 p-5">
        <h2 class="font-semibold text-slate-900">Import Soal dari Excel</h2>
        <p class="text-sm text-slate-500">Kolom: Pertanyaan, Opsi A–E, Kunci (A–E). Baris pertama adalah header. Gambar ditambahkan lewat tombol Edit setelah import.</p>
        <a href="{{ route('guru.soal.template', $ujian) }}" class="inline-block text-sm text-blue-600 hover:underline">⬇ Download template Excel</a>
        <a href="{{ route('guru.bank.index', ['ujian' => $ujian->id]) }}" class="ml-4 inline-block text-sm text-blue-600 hover:underline">📚 Ambil dari Bank Soal</a>
        <form action="{{ route('guru.soal.import', $ujian) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls" required
                   class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-900">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="hapus_lama" value="1"> Hapus semua soal lama sebelum import
            </label>
            <button class="btn-primary">Import</button>
        </form>
    </div>

    {{-- Soal manual --}}
    <form action="{{ route('guru.soal.store', $ujian) }}" method="POST" enctype="multipart/form-data" class="card space-y-3 p-5">
        @csrf
        <h2 class="font-semibold text-slate-900">Tambah Soal Manual</h2>

        <textarea id="in-tanya" name="pertanyaan" rows="3" placeholder="Pertanyaan. Rumus: $x^2 + 3x$" required
                  class="{{ $field }}">{{ old('pertanyaan') }}</textarea>
        <div class="rounded-lg bg-slate-50 p-3 text-sm">
            <div class="mb-1 text-xs font-semibold text-slate-400">PRATINJAU</div>
            <div id="pv-tanya" class="whitespace-pre-line text-slate-700">-</div>
        </div>

        <div class="rounded-lg border border-dashed border-slate-300 p-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">Gambar soal (opsional)</label>
            <input type="file" name="gambar" id="in-gambar" accept="image/png,image/jpeg"
                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:font-medium file:text-blue-700">
            <p class="mt-1 text-xs text-slate-400">JPG atau PNG, maksimal 2 MB.</p>
            <img id="pv-gambar" class="mt-2 hidden max-h-48 rounded-lg border" alt="Pratinjau gambar">
        </div>

        @foreach (['a', 'b', 'c', 'd', 'e'] as $o)
            <div class="flex items-center gap-2">
                <span class="w-6 font-semibold uppercase text-slate-700">{{ $o }}.</span>
                <input type="text" name="opsi_{{ $o }}" value="{{ old('opsi_'.$o) }}"
                       placeholder="Opsi {{ strtoupper($o) }}" required class="{{ $field }} flex-1">
            </div>
        @endforeach

        <div class="flex items-center gap-2">
            <label for="in-kunci" class="text-sm text-slate-700">Kunci jawaban:</label>
            <select id="in-kunci" name="kunci" required
                    style="color-scheme: light"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-900">
                @foreach (['A', 'B', 'C', 'D', 'E'] as $k)
                    <option value="{{ $k }}" class="bg-white text-slate-900" @selected(old('kunci', 'A') == $k)>{{ $k }}</option>
                @endforeach
            </select>
            <button class="btn-success ml-auto">Tambah</button>
        </div>
    </form>
</div>

{{-- Daftar soal --}}
<div class="card overflow-x-auto">
    <div class="border-b border-slate-100 p-4 font-semibold text-slate-900">Daftar Soal ({{ $ujian->soals->count() }})</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-600">
            <tr><th class="w-12 p-3">No</th><th class="p-3">Pertanyaan &amp; Opsi</th><th class="w-20 p-3">Kunci</th><th class="w-32 p-3"></th></tr>
        </thead>
        <tbody>
        @forelse ($ujian->soals as $i => $s)
            <tr class="border-t border-slate-100 align-top">
                <td class="p-3">{{ $i + 1 }}</td>
                <td class="p-3">
                    <div class="whitespace-pre-line text-slate-900">{{ $s->pertanyaan }}</div>
                    @if ($s->gambar_exists)
                        <img src="{{ route('soal.gambar', $s) }}?v={{ $s->updated_at->timestamp }}" class="mt-2 max-h-24 rounded border" alt="Gambar soal">
                    @endif
                    <ol class="mt-2 space-y-0.5 text-slate-600">
                        @foreach ($s->opsi as $huruf => $teks)
                            <li class="{{ $huruf === $s->kunci ? 'font-semibold text-green-700' : '' }}">
                                {{ $huruf }}. {{ $teks }}@if ($huruf === $s->kunci) ✓@endif
                            </li>
                        @endforeach
                    </ol>
                </td>
                <td class="p-3 font-bold">{{ $s->kunci }}</td>
                <td class="space-x-3 whitespace-nowrap p-3 text-right">
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

@push('scripts')
<script>
    const inTanya = document.getElementById('in-tanya');
    const pvTanya = document.getElementById('pv-tanya');
    let timer;
    inTanya.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            pvTanya.textContent = inTanya.value || '-';
            if (window.renderMath) window.renderMath(pvTanya);
        }, 250);
    });

    document.getElementById('in-gambar').addEventListener('change', (e) => {
        const f = e.target.files[0], img = document.getElementById('pv-gambar');
        if (!f) { img.classList.add('hidden'); return; }
        img.src = URL.createObjectURL(f);
        img.classList.remove('hidden');
    });
</script>
@endpush
@endsection