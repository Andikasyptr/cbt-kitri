@extends('layouts.app')
@section('title', $role === 'guru' ? 'Akun Guru' : 'Akun Siswa')
@section('content')
@php
    $guru = $role === 'guru';
    $label = $guru ? 'Guru' : 'Siswa';
    $induk = $guru ? 'NIP' : 'NIS';
    $routeStore = $guru ? 'admin.guru.store' : 'admin.siswa.store';
    $routeImport = $guru ? 'admin.guru.import' : 'admin.siswa.import';
    $routeTemplate = $guru ? 'admin.guru.template' : 'admin.siswa.template';
    $warna = $guru ? 'bg-violet-100 text-violet-700' : 'bg-emerald-100 text-emerald-700';
    $inisial = fn ($n) => collect(explode(' ', trim($n)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $filterAktif = request('q') || request('kelas_id') || request('status');
    $pilihanPerHalaman = [10, 25, 50, 100, 250, 500];
@endphp
 
{{-- Header halaman --}}
<div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Data {{ $label }}</h2>
        <p class="text-sm text-slate-500">
            <span class="font-semibold text-slate-700">{{ number_format($total) }}</span> akun {{ strtolower($label) }} terdaftar
            @unless ($guru)
                &middot; <span class="font-semibold text-red-600">{{ number_format($totalBlokir) }}</span> diblokir
            @endunless
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @unless ($guru)
            <button type="button" onclick="document.getElementById('dlg-blokir').showModal()" class="btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/30">
                Blokir Akses (SPP)
            </button>
        @endunless
        <a href="{{ route($routeTemplate) }}" class="btn-ghost">Template</a>
        <button type="button" onclick="document.getElementById('dlg-import').showModal()" class="btn-success">Import Excel</button>
        <button type="button" onclick="document.getElementById('dlg-tambah').showModal()" class="btn-primary">Tambah {{ $label }}</button>
    </div>
</div>
 
{{-- Pencarian & filter --}}
<form method="GET" class="card mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:p-4">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama, username, atau {{ $induk }}" class="flex-1 px-3 py-2">
    @unless ($guru)
        <select name="kelas_id" class="px-3 py-2 sm:w-52">
            <option value="">Semua kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_lengkap }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 sm:w-40">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="blokir" @selected(request('status') === 'blokir')>Diblokir</option>
        </select>
    @endunless
    <select name="per_page" class="px-3 py-2 sm:w-32" title="Jumlah data per halaman" onchange="this.form.submit()">
        @foreach ($pilihanPerHalaman as $n)
            <option value="{{ $n }}" @selected((int) request('per_page', 10) === $n)>{{ $n }} / hal</option>
        @endforeach
    </select>
    <button class="btn bg-slate-800 text-white hover:bg-slate-900">Cari</button>
    @if ($filterAktif)
        <a href="{{ url()->current() }}?per_page={{ request('per_page', 10) }}" class="btn-ghost">Reset</a>
    @endif
</form>
 
{{-- Aksi massal untuk siswa yang dicentang --}}
@unless ($guru)
    <form id="form-massal" action="{{ route('admin.siswa.blokir.massal') }}" method="POST"
          class="card mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
        @csrf
        <input type="hidden" name="mode" value="pilihan">
        <span class="text-sm font-medium text-slate-700">
            Siswa yang dicentang: <b id="jml-dipilih" class="text-blue-600">0</b>
        </span>
        <select name="aksi" class="px-3 py-2 sm:w-40">
            <option value="blokir">Blokir</option>
            <option value="buka">Buka akses</option>
        </select>
        <input name="alasan" placeholder="Alasan (default: Belum membayar SPP)" class="flex-1 px-3 py-2">
        <button class="btn bg-slate-800 text-white hover:bg-slate-900">Terapkan</button>
    </form>
@endunless
 
{{-- Tabel (desktop) --}}
<div class="card hidden overflow-hidden md:block">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            <tr>
                @unless ($guru)
                    <th class="w-10 px-4 py-3"><input type="checkbox" id="cek-semua" title="Pilih semua di halaman ini"></th>
                @endunless
                <th class="px-4 py-3">{{ $label }}</th>
                <th class="px-4 py-3">{{ $induk }}</th>
                <th class="px-4 py-3">Username</th>
                @unless ($guru)
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Akses</th>
                @endunless
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($users as $u)
            <tr class="{{ $u->diblokir ? 'bg-red-50/40' : 'hover:bg-slate-50/70' }}">
                @unless ($guru)
                    <td class="px-4 py-3"><input type="checkbox" class="cek-baris" value="{{ $u->id }}"></td>
                @endunless
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $warna }}">{{ $inisial($u->name) }}</div>
                        <span class="font-medium text-slate-900">{{ $u->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $u->nomor_induk ?: '-' }}</td>
                <td class="px-4 py-3"><span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">{{ $u->username }}</span></td>
                @unless ($guru)
                    <td class="px-4 py-3">
                        @if ($u->kelas)
                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $u->kelas->nama_lengkap }}</span>
                        @else - @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($u->diblokir)
                            <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Diblokir</span>
                            <div class="mt-0.5 max-w-[14rem] truncate text-xs text-slate-500" title="{{ $u->alasan_blokir }}">{{ $u->alasan_blokir }}</div>
                        @else
                            <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Aktif</span>
                        @endif
                    </td>
                @endunless
                <td class="whitespace-nowrap px-4 py-3 text-right">
                    @unless ($guru)
                        <form action="{{ route('admin.siswa.blokir', $u) }}" method="POST" class="inline"
                              onsubmit="return setAlasan(this, {{ $u->diblokir ? 'true' : 'false' }})">
                            @csrf
                            <input type="hidden" name="alasan" value="">
                            <button class="rounded-lg px-2.5 py-1.5 text-xs font-medium {{ $u->diblokir ? 'text-green-700 hover:bg-green-50' : 'text-amber-700 hover:bg-amber-50' }}">
                                {{ $u->diblokir ? 'Buka akses' : 'Blokir' }}
                            </button>
                        </form>
                    @endunless
                    <form action="{{ route('admin.akun.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun {{ e($u->name) }}?')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">Belum ada data {{ strtolower($label) }}.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
 
{{-- Kartu (HP) --}}
<div class="space-y-3 md:hidden">
    @forelse ($users as $u)
        <div class="card p-4 {{ $u->diblokir ? 'ring-red-200' : '' }}">
            <div class="flex items-start gap-3">
                @unless ($guru)
                    <input type="checkbox" class="cek-baris mt-3" value="{{ $u->id }}">
                @endunless
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $warna }}">{{ $inisial($u->name) }}</div>
                <div class="min-w-0 flex-1">
                    <div class="truncate font-semibold text-slate-900">{{ $u->name }}</div>
                    <div class="text-xs text-slate-500">{{ $induk }}: {{ $u->nomor_induk ?: '-' }}</div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">{{ $u->username }}</span>
                        @unless ($guru)
                            @if ($u->kelas)
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $u->kelas->nama_lengkap }}</span>
                            @endif
                            @if ($u->diblokir)
                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Diblokir</span>
                            @else
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Aktif</span>
                            @endif
                        @endunless
                    </div>
                    @if (! $guru && $u->diblokir && $u->alasan_blokir)
                        <div class="mt-1 text-xs text-slate-500">{{ $u->alasan_blokir }}</div>
                    @endif
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2 border-t border-slate-100 pt-3">
                @unless ($guru)
                    <form action="{{ route('admin.siswa.blokir', $u) }}" method="POST" onsubmit="return setAlasan(this, {{ $u->diblokir ? 'true' : 'false' }})">
                        @csrf
                        <input type="hidden" name="alasan" value="">
                        <button class="rounded-lg px-3 py-1.5 text-xs font-medium {{ $u->diblokir ? 'text-green-700 hover:bg-green-50' : 'text-amber-700 hover:bg-amber-50' }}">
                            {{ $u->diblokir ? 'Buka akses' : 'Blokir' }}
                        </button>
                    </form>
                @endunless
                <form action="{{ route('admin.akun.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun {{ e($u->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card p-10 text-center text-sm text-slate-400">Belum ada data {{ strtolower($label) }}.</div>
    @endforelse
</div>
 
<div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-xs text-slate-500">
        Menampilkan {{ number_format($users->firstItem() ?? 0) }}&ndash;{{ number_format($users->lastItem() ?? 0) }}
        dari {{ number_format($users->total()) }} data
    </p>
    <div>{{ $users->links() }}</div>
</div>
 
{{-- ============ Dialog: Tambah ============ --}}
<dialog id="dlg-tambah" class="w-[calc(100%-2rem)] max-w-md rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <form action="{{ route($routeStore) }}" method="POST" class="space-y-4 p-6">
        @csrf
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Tambah {{ $label }}</h3>
            <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup">&#10005;</button>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Lengkap</label>
            <input name="name" value="{{ old('name') }}" required class="w-full px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ $induk }}</label>
            <input name="nomor_induk" value="{{ old('nomor_induk') }}" class="w-full px-3 py-2">
        </div>
        @unless ($guru)
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Kelas</label>
                <select name="kelas_id" required class="w-full px-3 py-2">
                    <option value="">-- pilih kelas --</option>
                    @foreach ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
        @endunless
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                <input name="username" value="{{ old('username') }}" autocapitalize="none" required class="w-full px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                <input type="text" name="password" required minlength="6" class="w-full px-3 py-2">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="this.closest('dialog').close()" class="btn-ghost">Batal</button>
            <button class="btn-primary">Simpan</button>
        </div>
    </form>
</dialog>
 
{{-- ============ Dialog: Import akun ============ --}}
<dialog id="dlg-import" class="w-[calc(100%-2rem)] max-w-lg rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <form action="{{ route($routeImport) }}" method="POST" enctype="multipart/form-data" class="space-y-4 p-6">
        @csrf
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Import {{ $label }} dari Excel</h3>
            <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup">&#10005;</button>
        </div>
        <ol class="space-y-2 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
            <li><b>1.</b> Unduh template: <a href="{{ route($routeTemplate) }}" class="font-medium text-blue-600 hover:underline">template-akun-{{ $role }}.xlsx</a></li>
            <li><b>2.</b> Isi data mulai baris ke-2. Kolom: {{ $guru ? 'Nama, NIP, Username, Password' : 'Nama, NIS, Kelas, Username, Password' }}.</li>
            @unless ($guru)
                <li><b>3.</b> Kolom Kelas harus sama dengan nama di sheet <i>Daftar Kelas</i> (contoh: XII TKJ A).</li>
            @endunless
            <li><b>{{ $guru ? '3' : '4' }}.</b> Username/password kosong otomatis memakai {{ $induk }}. Password minimal 6 karakter.</li>
        </ol>
        <input type="file" name="file" accept=".xlsx,.xls" required
               class="block w-full rounded-lg border border-slate-300 p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:font-medium file:text-blue-700">
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="this.closest('dialog').close()" class="btn-ghost">Batal</button>
            <button id="btn-do-import" class="btn-success">Import Sekarang</button>
        </div>
    </form>
</dialog>
 
@unless ($guru)
{{-- ============ Dialog: Blokir akses (SPP) ============ --}}
<dialog id="dlg-blokir" class="w-[calc(100%-2rem)] max-w-lg rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="space-y-5 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Blokir / Buka Akses Siswa</h3>
            <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup">&#10005;</button>
        </div>
 
        <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">
            Siswa yang diblokir tidak bisa login dan langsung dikeluarkan jika sedang online. Akun, nilai, dan data lain tetap aman.
            Untuk memblokir satu per satu, atau memilih beberapa siswa, gunakan tombol di tabel.
        </p>
 
        {{-- Per kelas --}}
        <form action="{{ route('admin.siswa.blokir.massal') }}" method="POST" class="space-y-3 rounded-xl border border-slate-200 p-4">
            @csrf
            <input type="hidden" name="mode" value="kelas">
            <div class="text-sm font-semibold text-slate-800">Berdasarkan kelas</div>
            <select name="kelas_id" required class="w-full px-3 py-2">
                <option value="">-- pilih kelas --</option>
                <option value="semua">Semua kelas (hanya untuk buka akses)</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_lengkap }}</option>
                @endforeach
            </select>
            <div class="grid gap-2 sm:grid-cols-3">
                <select name="aksi" class="px-3 py-2">
                    <option value="blokir">Blokir</option>
                    <option value="buka">Buka akses</option>
                </select>
                <input name="alasan" placeholder="Alasan (opsional)" class="px-3 py-2 sm:col-span-2">
            </div>
            <button class="btn bg-slate-800 text-white hover:bg-slate-900" onclick="return confirm('Terapkan ke seluruh siswa di kelas yang dipilih?')">Terapkan ke kelas</button>
        </form>
 
        {{-- Dari Excel --}}
        <form action="{{ route('admin.siswa.blokir.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-slate-200 p-4">
            @csrf
            <div class="text-sm font-semibold text-slate-800">Dari daftar NIS (Excel)</div>
            <p class="text-xs text-slate-500">Kolom A berisi NIS (satu per baris), baris pertama adalah header. Cocok untuk daftar tunggakan dari bagian keuangan.</p>
            <input type="file" name="file" accept=".xlsx,.xls" required
                   class="block w-full rounded-lg border border-slate-300 p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:font-medium file:text-blue-700">
            <div class="grid gap-2 sm:grid-cols-3">
                <select name="aksi" class="px-3 py-2">
                    <option value="blokir">Blokir</option>
                    <option value="buka">Buka akses</option>
                </select>
                <input name="alasan" placeholder="Alasan (opsional)" class="px-3 py-2 sm:col-span-2">
            </div>
            <button class="btn bg-slate-800 text-white hover:bg-slate-900">Proses file</button>
        </form>
    </div>
</dialog>
@endunless
 
@push('scripts')
<script>
    // Buka kembali dialog tambah jika validasi gagal
    @if ($errors->any() && old('username') && ! $errors->has('import') && ! $errors->has('file') && ! $errors->has('blokir'))
        document.getElementById('dlg-tambah').showModal();
    @endif
 
    // Cegah klik ganda saat import
    document.querySelector('#dlg-import form').addEventListener('submit', function () {
        const b = document.getElementById('btn-do-import');
        b.disabled = true; b.textContent = 'Memproses...';
    });
 
    // Tanya alasan saat memblokir satu siswa; konfirmasi saat membuka akses
    function setAlasan(form, sudahDiblokir) {
        if (sudahDiblokir) return confirm('Buka akses siswa ini?');
        const alasan = prompt('Alasan blokir (boleh diubah):', 'Belum membayar SPP');
        if (alasan === null) return false;
        form.querySelector('input[name=alasan]').value = alasan;
        return true;
    }
 
    // ===== Pilih siswa (centang) =====
    // Hanya checkbox yang terlihat (desktop ATAU HP) yang dihitung
    const barisTerlihat = () =>
        [...document.querySelectorAll('.cek-baris')].filter((c) => c.offsetParent !== null);
 
    const cekSemua = document.getElementById('cek-semua');
    const jml = document.getElementById('jml-dipilih');
 
    const hitung = () => {
        if (!jml) return;
        const semua = barisTerlihat();
        const n = semua.filter((c) => c.checked).length;
        jml.textContent = n;
        if (cekSemua) {
            cekSemua.checked = semua.length > 0 && n === semua.length;
            cekSemua.indeterminate = n > 0 && n < semua.length;
        }
    };
 
    if (cekSemua) {
        cekSemua.addEventListener('change', () => {
            barisTerlihat().forEach((c) => (c.checked = cekSemua.checked));
            hitung();
        });
    }
    document.querySelectorAll('.cek-baris').forEach((c) => c.addEventListener('change', hitung));
 
    // Kumpulkan ID yang dicentang saat form massal dikirim
    const formMassal = document.getElementById('form-massal');
    if (formMassal) {
        formMassal.addEventListener('submit', function (e) {
            const ids = new Set(barisTerlihat().filter((c) => c.checked).map((c) => c.value));
            if (ids.size === 0) {
                e.preventDefault();
                alert('Pilih minimal satu siswa.');
                return;
            }
            const aksi = this.querySelector('[name=aksi]').value === 'blokir' ? 'Blokir' : 'Buka akses';
            if (!confirm(`${aksi} ${ids.size} siswa?`)) {
                e.preventDefault();
                return;
            }
            this.querySelectorAll('input[name="ids[]"]').forEach((i) => i.remove());
            ids.forEach((v) => {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'ids[]';
                i.value = v;
                this.appendChild(i);
            });
        });
    }
</script>
@endpush
@endsection
 