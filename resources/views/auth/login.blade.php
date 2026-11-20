<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - CBT SMKS Kitri Bakti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
<div class="min-h-screen lg:grid lg:grid-cols-2">

    {{-- ============ Panel brand ============ --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white
                    px-6 pt-10 pb-16 sm:px-10 lg:flex lg:flex-col lg:justify-between lg:p-14">
        {{-- dekorasi --}}
        <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-blue-400/20 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25 backdrop-blur">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.42A12.08 12.08 0 0121 17.5M12 14l-6.16-3.42A12.08 12.08 0 003 17.5M12 14v7"/>
                </svg>
            </div>
            <div>
                <div class="text-base font-bold leading-tight">SMKS Kitri Bakti</div>
                <div class="text-xs text-blue-200">Computer Based Test</div>
            </div>
        </div>

        <div class="relative mt-10 lg:mt-0">
            <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">
                Ujian digital,<br class="hidden lg:block"> lebih <span class="text-blue-300">mudah</span> dan terpercaya.
            </h1>
            <p class="mt-4 max-w-md text-sm text-blue-100 sm:text-base">
                Platform ujian berbasis komputer untuk siswa, guru, dan admin SMKS Kitri Bakti.
            </p>

            <ul class="mt-8 hidden gap-4 text-sm text-blue-50 lg:grid">
                <li class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">✓</span>
                    Ujian dengan token dan timer otomatis
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">✓</span>
                    Mode layar penuh dengan pemantauan pelanggaran
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">✓</span>
                    Nilai langsung tersedia untuk guru
                </li>
            </ul>
        </div>

        <div class="relative mt-10 hidden text-xs text-blue-200 lg:block">
            &copy; {{ date('Y') }} SMKS Kitri Bakti. Seluruh hak dilindungi.
        </div>
    </section>

    {{-- ============ Form login ============ --}}
    <section class="relative -mt-8 flex items-start justify-center px-4 pb-10 sm:px-6 lg:mt-0 lg:items-center lg:px-12 lg:pb-0">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl shadow-slate-900/10 ring-1 ring-slate-200
                    sm:p-8 lg:rounded-none lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0">

            <div class="mb-7">
                <h2 class="text-2xl font-bold text-slate-900">Selamat datang 👋</h2>
                <p class="mt-1 text-sm text-slate-500">Masuk dengan akun yang diberikan oleh sekolah.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-700" role="alert">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="form-login" action="{{ route('login') }}" method="POST" class="space-y-5" novalidate>
                @csrf

                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0"/>
                            </svg>
                        </span>
                        <input id="username" type="text" name="username" value="{{ old('username') }}"
                               autocomplete="username" autocapitalize="none" spellcheck="false" autofocus required
                               placeholder="Masukkan username"
                               class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-base text-slate-900
                                      placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15
                                      @error('username') border-red-400 @enderror">
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 10-9 0v3m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5H6a1.5 1.5 0 01-1.5-1.5V12A1.5 1.5 0 016 10.5z"/>
                            </svg>
                        </span>
                        <input id="password" type="password" name="password" autocomplete="current-password" required
                               placeholder="Masukkan password"
                               class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-12 text-base text-slate-900
                                      placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <button type="button" id="toggle-pw" aria-label="Tampilkan password"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-slate-600">
                            <svg id="ico-eye" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg id="ico-eye-off" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Ingat saya
                    </label>
                    <span class="text-xs text-slate-400">Lupa password? Hubungi admin.</span>
                </div>

                <button id="btn-login" type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-base font-semibold text-white
                               shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30
                               active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70">
                    <svg id="spin" class="hidden h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span id="btn-text">Masuk</span>
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-slate-400 lg:hidden">
                &copy; {{ date('Y') }} SMKS Kitri Bakti
            </p>
        </div>
    </section>
</div>

<script>
    // Tampilkan / sembunyikan password
    const pw = document.getElementById('password');
    document.getElementById('toggle-pw').addEventListener('click', () => {
        const tampil = pw.type === 'password';
        pw.type = tampil ? 'text' : 'password';
        document.getElementById('ico-eye').classList.toggle('hidden', tampil);
        document.getElementById('ico-eye-off').classList.toggle('hidden', !tampil);
    });

    // Cegah klik ganda saat proses login
    document.getElementById('form-login').addEventListener('submit', function () {
        const btn = document.getElementById('btn-login');
        btn.disabled = true;
        document.getElementById('spin').classList.remove('hidden');
        document.getElementById('btn-text').textContent = 'Memproses...';
    });
</script>
</body>
</html>