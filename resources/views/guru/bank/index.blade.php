@extends('layouts.app')
@section('title', 'Bank Soal')
@section('content')

{{-- Header --}}
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Bank Soal</h2>
        <p class="text-sm text-slate-500">
            <span class="font-semibold text-slate-700">{{ number_format($total) }}</span> soal tersimpan.
            Soal masuk otomatis setelah ujiannya dikerjakan siswa.
        </p>
    </div>
    <form action="{{ route('guru.bank.sinkron') }}" method="POST"
          onsubmit="return confirm('Salin soal dari semua ujian lama yang sudah dikerjakan ke bank?')">
        @csrf
        <button class="btn-ghost">Sinkronkan ujian lama</button>
    </form>
</div>

{{-- Filter --}}
<form method="GET" class="card mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:p-4">
    @if ($target) <input type="hidden" name="ujian" value="{{ $target }}"> @endif
    <input name="q" value="{{ request('q') }}" placeholder="Cari isi pertanyaan" class="flex-1 px-3 py-2">
    <select name="mapel_id" class="px-3 py-2 sm:w-56">
        <option value="">Semua mapel</option>
        @foreach ($mapels as $m)
            <option value="{{ $m->id }}" @selected(request('mapel_id') == $m->id)>{{ $m->nama }}</option>
        @endforeach
    </select>
    <button class="btn bg-slate-800 text-white hover:bg-slate-900">Cari</button>
    @if (request('q') || request('mapel_id'))
        <a href="{{ route('guru.bank.index', $target ? ['ujian' => $target] : []) }}" class="btn-ghost">Reset</a>
    @endif
</form>

{{-- Form pilih soal --}}
<form id="form-tambah" action="{{ route('guru.bank.tambah') }}" method="POST">
    @csrf

    <div class="card sticky top-14 z-10 mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:items-center lg:top-2">
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" id="cek-semua"> Pilih semua di halaman ini
        </label>
        <span class="text-sm text-slate-500 sm:ml-auto">Terpilih: <b id="jml-pilih">0</b></span>
        <select name="ujian_id" required class="px-3 py-2 sm:w-72">
            <option value="">-- Ujian tujuan --</option>
            @foreach ($ujians as $u)
                <option value="{{ $u->id }}" @selected($target === $u->id)>{{ $u->judul }}</option>
            @endforeach
        </select>
        <button class="btn-primary" @disabled($ujians->isEmpty())>Tambahkan ke ujian</button>
    </div>

    @if ($ujians->isEmpty())
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
            Belum ada ujian tujuan. Buat ujian baru dulu (ujian yang sudah dikerjakan siswa tidak bisa ditambah soal).
            <a href="{{ route('guru.ujian.create') }}" class="font-medium underline">Buat ujian</a>
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($soals as $s)
            <div class="card p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="ids[]" value="{{ $s->id }}" class="cek-baris mt-1">
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 font-medium text-blue-700">{{ $s->mapel->nama ?? 'Tanpa mapel' }}</span>
                            @if ($s->sumber)
                                <span class="text-slate-500">dari: {{ $s->sumber }}</span>
                            @endif
                            <span class="text-slate-400">&middot; dipakai {{ $s->dipakai ?? 0 }}x</span>
                            <span class="ml-auto rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Kunci: {{ $s->kunci }}</span>
                        </div>

                        <p class="whitespace-pre-line text-slate-900">{{ $s->pertanyaan }}</p>

                        @if ($s->ada_gambar)
                            <img src="{{ route('guru.bank.gambar', $s) }}?v={{ $s->updated_at->timestamp }}" class="mt-3 max-h-56 rounded-lg border" alt="Gambar soal" loading="lazy">
                        @endif

                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($s->opsi as $huruf => $teks)
                                @php $benar = $huruf === $s->kunci; @endphp
                                <div class="flex gap-2 rounded-lg border px-3 py-2 text-sm {{ $benar ? 'border-green-300 bg-green-50' : 'border-slate-200' }}">
                                    <span class="font-bold {{ $benar ? 'text-green-700' : '' }}">{{ $huruf }}.</span>
                                    <span class="whitespace-pre-line">{{ $teks }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" form="hapus-{{ $s->id }}"
                            class="shrink-0 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                            onclick="return confirm('Hapus soal ini dari bank?')">Hapus</button>
                </div>
            </div>
        @empty
            <div class="card p-10 text-center text-sm text-slate-400">
                Bank soal masih kosong. Soal akan masuk otomatis setelah siswa mulai mengerjakan ujian.
                Untuk ujian yang sudah pernah dikerjakan, klik <b>Sinkronkan ujian lama</b>.
            </div>
        @endforelse
    </div>
</form>

{{-- Form hapus (di luar form pilih soal agar tidak bersarang) --}}
@foreach ($soals as $s)
    <form id="hapus-{{ $s->id }}" action="{{ route('guru.bank.destroy', $s) }}" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach

<div class="mt-4">{{ $soals->links() }}</div>

@push('scripts')
<script>
    const cekBaris = () => [...document.querySelectorAll('.cek-baris')];
    const hitung = () => document.getElementById('jml-pilih').textContent = cekBaris().filter((c) => c.checked).length;
    document.getElementById('cek-semua').addEventListener('change', (e) => {
        cekBaris().forEach((c) => (c.checked = e.target.checked));
        hitung();
    });
    cekBaris().forEach((c) => c.addEventListener('change', hitung));
</script>
@endpush
@endsection