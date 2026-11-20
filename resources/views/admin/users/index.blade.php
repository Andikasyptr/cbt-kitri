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
@endphp

{{-- Header halaman --}}
<div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Data {{ $label }}</h2>
        <p class="text-sm text-slate-500">
            <span class="font-semibold text-slate-700">{{ number_format($total) }}</span> akun {{ strtolower($label) }} terdaftar
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route($routeTemplate) }}" class="btn-ghost">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Template
        </a>
        <button type="button" onclick="document.getElementById('dlg-import').showModal()" class="btn-success">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            Import Excel
        </button>
        <button type="button" onclick="document.getElementById('dlg-tambah').showModal()" class="btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah {{ $label }}
        </button>
    </div>
</div>

{{-- Pencarian & filter --}}
<form method="GET" class="card mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:p-4">
    <div class="relative flex-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        </span>
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama, username, atau {{ $induk }}" class="w-full py-2 pl-10 pr-3">
    </div>
    @unless ($guru)
        <select name="kelas_id" class="px-3 py-2 sm:w-56">
            <option value="">Semua kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_lengkap }}</option>
            @endforeach
        </select>
    @endunless
    <button class="btn bg-slate-800 text-white hover:bg-slate-900">Cari</button>
    @if (request('q') || request('kelas_id'))
        <a href="{{ url()->current() }}" class="btn-ghost">Reset</a>
    @endif
</form>

{{-- Tabel (desktop) --}}
<div class="card hidden overflow-hidden md:block">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">{{ $label }}</th>
                <th class="px-4 py-3">{{ $induk }}</th>
                <th class="px-4 py-3">Username</th>
                @unless ($guru) <th class="px-4 py-3">Kelas</th> @endunless
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($users as $u)
            <tr class="hover:bg-slate-50/70">
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
                @endunless
                <td class="px-4 py-3 text-right">
                    <form action="{{ route('admin.akun.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun {{ e($u->name) }}?')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">Belum ada data {{ strtolower($label) }}.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Kartu (HP) --}}
<div class="space-y-3 md:hidden">
    @forelse ($users as $u)
        <div class="card p-4">
            <div class="flex items-start gap-3">
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
                        @endunless
                    </div>
                </div>
                <form action="{{ route('admin.akun.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun {{ e($u->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card p-10 text-center text-sm text-slate-400">Belum ada data {{ strtolower($label) }}.</div>
    @endforelse
</div>

<div class="mt-4">{{ $users->links() }}</div>

{{-- ============ Dialog: Tambah ============ --}}
<dialog id="dlg-tambah" class="w-[calc(100%-2rem)] max-w-md rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <form action="{{ route($routeStore) }}" method="POST" class="space-y-4 p-6">
        @csrf
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Tambah {{ $label }}</h3>
            <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
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

{{-- ============ Dialog: Import ============ --}}
<dialog id="dlg-import" class="w-[calc(100%-2rem)] max-w-lg rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <form action="{{ route($routeImport) }}" method="POST" enctype="multipart/form-data" class="space-y-4 p-6">
        @csrf
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Import {{ $label }} dari Excel</h3>
            <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <ol class="space-y-2 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
            <li><b>1.</b> Unduh template: <a href="{{ route($routeTemplate) }}" class="font-medium text-blue-600 hover:underline">template-akun-{{ $role }}.xlsx</a></li>
            <li><b>2.</b> Isi data mulai baris ke-2. Kolom: {{ $guru ? 'Nama, NIP, Username, Password' : 'Nama, NIS, Kelas, Username, Password' }}.</li>
            @unless ($guru)
                <li><b>3.</b> Kolom Kelas harus sama dengan nama kelas di sheet <i>Daftar Kelas</i> (contoh: XII TKJ A).</li>
            @endunless
            <li><b>{{ $guru ? '3' : '4' }}.</b> Username/password kosong otomatis memakai {{ $induk }}. Password minimal 6 karakter.</li>
        </ol>

        <input type="file" name="file" accept=".xlsx,.xls" required
               class="block w-full rounded-lg border border-slate-300 p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:font-medium file:text-blue-700 hover:file:bg-blue-100">

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="this.closest('dialog').close()" class="btn-ghost">Batal</button>
            <button id="btn-do-import" class="btn-success">Import Sekarang</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    // Buka kembali dialog tambah jika validasi gagal
    @if ($errors->any() && old('username') && ! $errors->has('import') && ! $errors->has('file'))
        document.getElementById('dlg-tambah').showModal();
    @endif
    // Cegah klik ganda saat import (proses bisa beberapa detik)
    document.querySelector('#dlg-import form').addEventListener('submit', function () {
        const b = document.getElementById('btn-do-import');
        b.disabled = true; b.textContent = 'Memproses...';
    });
</script>
@endpush
@endsection