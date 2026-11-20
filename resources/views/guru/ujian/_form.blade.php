@php
    $u = $ujian ?? null;
    $dipilih = old('kelas_ids', $u ? $u->kelas->pluck('id')->all() : []);
    $acak = old('acak_soal', $u ? $u->acak_soal : true);
@endphp

<div class="grid md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm mb-1">Judul Ujian</label>
        <input name="judul" value="{{ old('judul', $u?->judul) }}" placeholder="PAS Pendidikan Agama Islam" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Mata Pelajaran</label>
        <select name="mapel_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">-- pilih --</option>
            @foreach ($mapels as $m)
                <option value="{{ $m->id }}" @selected(old('mapel_id', $u?->mapel_id) == $m->id)>{{ $m->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Durasi (menit)</label>
        <input type="number" name="durasi_menit" min="1" value="{{ old('durasi_menit', $u?->durasi_menit ?? 60) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Waktu Mulai (jadwal dibuka)</label>
        <input type="datetime-local" name="waktu_mulai" required
               value="{{ old('waktu_mulai', $u?->waktu_mulai?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Waktu Selesai (jadwal ditutup)</label>
        <input type="datetime-local" name="waktu_selesai" required
               value="{{ old('waktu_selesai', $u?->waktu_selesai?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm mb-2">Kelas Peserta</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach ($kelasList as $k)
                <label class="flex items-center gap-2 text-sm border rounded-lg px-3 py-2 hover:bg-slate-50">
                    <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" @checked(in_array($k->id, $dipilih))>
                    {{ $k->nama_lengkap }}
                </label>
            @endforeach
        </div>
    </div>
    <label class="md:col-span-2 flex items-center gap-2 text-sm">
        <input type="checkbox" name="acak_soal" value="1" @checked($acak)>
        Acak urutan soal untuk setiap siswa
    </label>
</div>

<div class="pt-2">
    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-5 py-2">Simpan</button>
    <a href="{{ route('guru.ujian.index') }}" class="ml-3 text-sm text-slate-500 hover:underline">Batal</a>
</div>