@extends('layouts.app')
@section('title', 'Edit Soal')
@section('content')
<form action="{{ route('guru.soal.update', $soal) }}" method="POST" class="bg-white rounded-xl shadow p-6 max-w-3xl space-y-3">
    @csrf @method('PUT')
    <textarea name="pertanyaan" rows="4" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
    @foreach (['a', 'b', 'c', 'd', 'e'] as $o)
        <div class="flex items-center gap-2">
            <span class="w-6 font-semibold uppercase">{{ $o }}.</span>
            <input name="opsi_{{ $o }}" value="{{ old('opsi_'.$o, $soal->{'opsi_'.$o}) }}" required
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
    @endforeach
    <div class="flex items-center gap-2">
        <label class="text-sm">Kunci jawaban:</label>
        <select name="kunci" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            @foreach (['A', 'B', 'C', 'D', 'E'] as $k)
                <option @selected(old('kunci', $soal->kunci) == $k)>{{ $k }}</option>
            @endforeach
        </select>
    </div>
    <div class="pt-2">
        <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-5 py-2">Simpan</button>
        <a href="{{ route('guru.ujian.show', $soal->ujian_id) }}" class="ml-3 text-sm text-slate-500 hover:underline">Batal</a>
    </div>
</form>
@endsection