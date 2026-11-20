@extends('layouts.app')
@section('title', 'Edit Soal')
@section('content')
<form action="{{ route('guru.soal.update', $soal) }}" method="POST" enctype="multipart/form-data" class="card max-w-3xl space-y-3 p-6">
    @csrf @method('PUT')

    <textarea name="pertanyaan" rows="4" required class="w-full px-3 py-2 text-sm">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>

    <div class="rounded-lg border border-dashed border-slate-300 p-3">
        <label class="mb-1 block text-sm font-medium text-slate-700">Gambar soal</label>

        @if ($soal->gambar_exists)
            <img src="{{ route('soal.gambar', $soal) }}?v={{ $soal->updated_at->timestamp }}" class="mb-2 max-h-48 rounded-lg border" alt="Gambar saat ini">
            <label class="mb-2 flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="hapus_gambar" value="1"> Hapus gambar ini
            </label>
        @endif

        <input type="file" name="gambar" accept="image/png,image/jpeg"
               class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:font-medium file:text-blue-700">
        <p class="mt-1 text-xs text-slate-400">{{ $soal->gambar_exists ? 'Pilih file baru untuk mengganti gambar.' : 'JPG atau PNG, maksimal 2 MB.' }}</p>
    </div>

    @foreach (['a', 'b', 'c', 'd', 'e'] as $o)
        <div class="flex items-center gap-2">
            <span class="w-6 font-semibold uppercase">{{ $o }}.</span>
            <input name="opsi_{{ $o }}" value="{{ old('opsi_'.$o, $soal->{'opsi_'.$o}) }}" required class="flex-1 px-3 py-1.5 text-sm">
        </div>
    @endforeach

    <div class="flex items-center gap-2">
        <label class="text-sm">Kunci jawaban:</label>
        <select name="kunci" class="px-3 py-1.5 text-sm">
            @foreach (['A', 'B', 'C', 'D', 'E'] as $k)
                <option @selected(old('kunci', $soal->kunci) == $k)>{{ $k }}</option>
            @endforeach
        </select>
    </div>

    <div class="pt-2">
        <button class="btn-primary px-5">Simpan</button>
        <a href="{{ route('guru.ujian.show', $soal->ujian_id) }}" class="ml-3 text-sm text-slate-500 hover:underline">Batal</a>
    </div>
</form>
@endsection