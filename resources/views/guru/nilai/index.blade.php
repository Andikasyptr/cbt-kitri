@extends('layouts.app')
@section('title', 'Nilai: ' . $ujian->judul)
@section('content')

<div class="flex flex-wrap items-center gap-3 mb-4 text-sm">
    <a href="{{ route('guru.ujian.show', $ujian) }}" class="text-blue-600 hover:underline">&larr; Kembali ke ujian</a>
    <a href="{{ route('guru.nilai.export', $ujian) }}" class="ml-auto bg-green-600 hover:bg-green-700 text-white rounded-lg px-4 py-2">⬇ Export Excel</a>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([['Sudah Selesai', $stat['selesai']], ['Rata-rata', $stat['rata']], ['Tertinggi', $stat['tertinggi']], ['Terendah', $stat['terendah']]] as [$label, $angka])
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-slate-500">{{ $label }}</div>
            <div class="text-2xl font-bold">{{ $angka }}</div>
        </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow overflow-x-auto mb-6">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="p-3">No</th><th class="p-3">Nama</th><th class="p-3">NIS</th><th class="p-3">Kelas</th>
                <th class="p-3">Status</th><th class="p-3">Benar</th><th class="p-3">Nilai</th>
                <th class="p-3">Pelanggaran</th><th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($hasils as $i => $h)
            <tr class="border-t">
                <td class="p-3">{{ $i + 1 }}</td>
                <td class="p-3">{{ $h->siswa->name }}</td>
                <td class="p-3">{{ $h->siswa->nomor_induk ?? '-' }}</td>
                <td class="p-3">{{ $h->siswa->kelas?->nama_lengkap ?? '-' }}</td>
                <td class="p-3">
                    @if ($h->selesai_at)
                        <span class="text-green-700">Selesai {{ $h->selesai_at->format('H:i') }}</span>
                    @else
                        <span class="text-yellow-700">Mengerjakan</span>
                    @endif
                </td>
                <td class="p-3">{{ $h->benar }} / {{ $ujian->soals()->count() }}</td>
                <td class="p-3 font-bold">{{ $h->nilai ?? '-' }}</td>
                <td class="p-3 {{ $h->pelanggaran > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $h->pelanggaran }}</td>
                <td class="p-3 text-right">
                    <form action="{{ route('guru.nilai.reset', $h) }}" method="POST"
                          onsubmit="return confirm('Reset hasil siswa ini? Nilai dihapus dan siswa bisa mengerjakan ulang.')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Reset</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="p-6 text-center text-slate-400">Belum ada siswa yang mengerjakan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($belum->count())
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold mb-2">Belum Mengerjakan ({{ $belum->count() }})</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1 text-sm text-slate-600">
            @foreach ($belum as $s)
                <div>{{ $s->name }} <span class="text-slate-400">({{ $s->kelas?->nama_lengkap }})</span></div>
            @endforeach
        </div>
    </div>
@endif
@endsection