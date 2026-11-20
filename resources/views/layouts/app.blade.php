<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - CBT SMKS Kitri Bakti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            /* Semua input di area konten: seragam, 16px agar iOS tidak zoom otomatis */
            main input:not([type=checkbox]):not([type=radio]):not([type=file]),
            main select,
            main textarea {
                @apply text-base sm:text-sm bg-white rounded-lg border-slate-300 transition
                       focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15;
            }
            main input[type=checkbox], main input[type=radio] { @apply h-4 w-4 text-blue-600 focus:ring-blue-500; }
        }
        @layer components {
            .card { @apply bg-white rounded-2xl shadow-sm ring-1 ring-slate-200; }
            .btn { @apply inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-4; }
            .btn-primary { @apply btn bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500/30; }
            .btn-success { @apply btn bg-green-600 text-white hover:bg-green-700 focus:ring-green-500/30; }
            .btn-danger  { @apply btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/30; }
            .btn-ghost   { @apply btn border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-300/40; }
        }
    </style>
    @include('partials.katex')
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800">
@php
    $user = auth()->user();
    $role = $user->role;

    $ikon = [
        'home'   => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'cap'    => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
        'grid'   => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'book'   => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'users'  => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'user'   => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'exam'   => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
        'out'    => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
        'menu'   => 'M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5',
        'close'  => 'M6 18L18 6M6 6l12 12',
    ];

    // [route, label, pola route aktif, ikon]
    $menu = match ($role) {
        'admin' => [
            ['admin.dashboard', 'Dashboard', 'admin.dashboard', 'home'],
            ['admin.jurusan.index', 'Jurusan', 'admin.jurusan.*', 'cap'],
            ['admin.kelas.index', 'Kelas', 'admin.kelas.*', 'grid'],
            ['admin.mapel.index', 'Mata Pelajaran', 'admin.mapel.*', 'book'],
            ['admin.guru.index', 'Akun Guru', 'admin.guru.*', 'users'],
            ['admin.siswa.index', 'Akun Siswa', 'admin.siswa.*', 'user'],
            ['admin.soal.index', 'Daftar Soal', 'admin.soal.*', 'exam'],
        ],
        'guru' => [
            ['guru.dashboard', 'Dashboard', 'guru.dashboard', 'home'],
            ['guru.ujian.index', 'Daftar Ujian', ['guru.ujian.*', 'guru.soal.*', 'guru.nilai.*'], 'exam'],
        ],
        default => [
            ['siswa.dashboard', 'Ujian Saya', 'siswa.*', 'exam'],
        ],
    };

    $inisial = collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
@endphp

{{-- Latar gelap saat menu HP terbuka --}}
<div id="backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

{{-- ============ Sidebar ============ --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-slate-900 text-slate-300
              transition-transform duration-200 ease-out lg:translate-x-0">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-800 px-5">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['cap'] }}"/></svg>
            </div>
            <div class="leading-tight">
                <div class="text-sm font-bold text-white">CBT Kitri Bakti</div>
                <div class="text-[11px] text-slate-400">SMKS Kitri Bakti</div>
            </div>
        </div>
        <button id="btn-close" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden" aria-label="Tutup menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['close'] }}"/></svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        <div class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu</div>
        @foreach ($menu as [$route, $label, $pola, $ic])
            @php $aktif = request()->routeIs($pola); @endphp
            <a href="{{ route($route) }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                      {{ $aktif ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0 {{ $aktif ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon[$ic] }}"/>
                </svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-slate-800 p-4">
        <div class="mb-3 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600/20 text-sm font-bold text-blue-300 ring-1 ring-blue-500/30">{{ $inisial }}</div>
            <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-white">{{ $user->name }}</div>
                <div class="text-xs capitalize text-slate-400">{{ $role }}</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-800 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-red-600 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['out'] }}"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>

{{-- ============ Konten ============ --}}
<div class="lg:pl-72">
    {{-- Top bar (hanya tampil di HP/tablet) --}}
    <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur lg:hidden">
        <button id="btn-menu" class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="Buka menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['menu'] }}"/></svg>
        </button>
        <div class="min-w-0 flex-1 truncate text-sm font-semibold">@yield('title', 'Dashboard')</div>
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ $inisial }}</div>
    </header>

    <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
        <div class="mb-5 hidden lg:block">
            <h1 class="text-2xl font-bold text-slate-900">@yield('title')</h1>
        </div>

        @if (session('success'))
            <div class="mb-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-3.5 text-sm text-green-800" role="status">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-700" role="alert">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('backdrop');
    const buka = () => { sidebar.classList.remove('-translate-x-full'); backdrop.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); };
    const tutup = () => { sidebar.classList.add('-translate-x-full'); backdrop.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); };
    document.getElementById('btn-menu').addEventListener('click', buka);
    document.getElementById('btn-close').addEventListener('click', tutup);
    backdrop.addEventListener('click', tutup);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
</script>
@stack('scripts')
</body>
</html>