<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ujian->judul }} - CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { user-select: none; -webkit-user-select: none; }</style>
    @include('partials.katex')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">

{{-- Overlay: wajib diklik untuk masuk fullscreen (browser mewajibkan aksi klik) --}}
<div id="overlay" class="fixed inset-0 z-50 bg-slate-900/95 flex items-center justify-center p-6 text-center text-white">
    <div class="max-w-md">
        <h2 id="overlay-judul" class="text-2xl font-bold mb-2">Ujian siap dimulai</h2>
        <p id="overlay-pesan" class="text-slate-300 text-sm mb-2">Klik tombol di bawah untuk masuk mode layar penuh. Waktu ujian sedang berjalan.</p>
        <p id="overlay-warn" class="text-red-400 font-semibold text-sm mb-6 min-h-[1.25rem]"></p>
        <button id="btn-fs" class="bg-blue-600 hover:bg-blue-700 rounded-lg px-6 py-3 font-semibold">Masuk Layar Penuh &amp; Mulai</button>
    </div>
</div>

{{-- Toast peringatan --}}
<div id="toast" class="hidden fixed top-20 left-1/2 -translate-x-1/2 z-40 bg-red-600 text-white text-sm rounded-lg shadow-lg px-4 py-2"></div>

{{-- Modal konfirmasi kumpulkan --}}
<div id="modal-selesai" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 max-w-sm w-full text-center space-y-4">
        <h3 class="font-bold text-lg">Kumpulkan ujian?</h3>
        <p id="modal-info" class="text-sm text-slate-600"></p>
        <div class="flex gap-2 justify-center">
            <button id="btn-batal" class="border rounded-lg px-4 py-2 hover:bg-slate-50">Kembali</button>
            <button id="btn-yakin" class="bg-green-600 hover:bg-green-700 text-white rounded-lg px-4 py-2">Ya, Kumpulkan</button>
        </div>
    </div>
</div>

<form id="form-selesai" action="{{ route('siswa.ujian.selesai', $hasil) }}" method="POST" class="hidden">@csrf</form>

{{-- Header --}}
<header class="bg-slate-900 text-white sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center gap-4">
        <div class="min-w-0">
            <div class="font-bold truncate">{{ $ujian->judul }}</div>
            <div class="text-xs text-slate-400">{{ auth()->user()->name }}</div>
        </div>
        <div class="ml-auto flex items-center gap-4 text-sm">
            <div class="text-slate-300">Pelanggaran: <span id="pelanggaran" class="font-bold text-white">{{ $hasil->pelanggaran }}</span>/{{ $batas }}</div>
            <div id="timer" class="font-mono text-xl font-bold bg-slate-800 rounded-lg px-3 py-1">--:--:--</div>
            <button id="btn-kumpul" class="bg-green-600 hover:bg-green-700 rounded-lg px-4 py-2">Kumpulkan</button>
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto p-4 grid lg:grid-cols-4 gap-4">
    {{-- Soal --}}
    <section class="lg:col-span-3 bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <div id="q-no" class="text-sm font-semibold text-blue-700"></div>
            <div id="status-simpan" class="text-xs text-slate-400"></div>
        </div>
        <div id="q-text" class="text-lg mb-6 whitespace-pre-line"></div>
        <div id="opsi" class="space-y-2"></div>

        <div class="flex justify-between mt-8">
            <button id="btn-prev" class="border rounded-lg px-5 py-2 hover:bg-slate-50 disabled:opacity-40">&larr; Sebelumnya</button>
            <button id="btn-next" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-5 py-2 disabled:opacity-40">Berikutnya &rarr;</button>
        </div>
    </section>

    {{-- Navigasi nomor --}}
    <aside class="bg-white rounded-xl shadow p-4 h-fit">
        <div class="font-semibold text-sm mb-3">Nomor Soal</div>
        <div id="grid" class="grid grid-cols-5 gap-2"></div>
        <div class="mt-4 text-xs text-slate-500 space-y-1">
            <div><span class="inline-block w-3 h-3 bg-blue-600 rounded mr-1 align-middle"></span> Sudah dijawab</div>
            <div><span class="inline-block w-3 h-3 border rounded mr-1 align-middle"></span> Belum dijawab</div>
        </div>
    </aside>
</main>

<script>
const SOAL = @json($soalData);
const JAWABAN = @json($jawaban);
const BATAS = {{ $batas }};
const URL_JAWAB = @json(route('siswa.ujian.jawab', $hasil));
const URL_PELANGGARAN = @json(route('siswa.ujian.pelanggaran', $hasil));
const csrf = document.querySelector('meta[name=csrf-token]').content;

const target = Date.now() + {{ $sisa }} * 1000; // batas waktu, dihitung dari sisa waktu server
let idx = 0, mulai = false, selesai = false, pelanggaran = {{ $hasil->pelanggaran }}, lastViolation = 0;

const $ = (id) => document.getElementById(id);

async function post(url, data = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(data),
    });
    return res.json().catch(() => ({}));
}

// ---------- Render soal ----------
function render() {
    const s = SOAL[idx];
    $('q-no').textContent = `Soal ${idx + 1} dari ${SOAL.length}`;
    $('q-text').textContent = s.pertanyaan;

    $('opsi').innerHTML = '';
    Object.entries(s.opsi).forEach(([huruf, teks]) => {
        const aktif = JAWABAN[s.id] === huruf;
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'w-full text-left flex gap-3 items-start border rounded-lg px-4 py-3 transition ' +
            (aktif ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-slate-50');
        const h = document.createElement('span'); h.className = 'font-bold'; h.textContent = huruf + '.';
        const t = document.createElement('span'); t.className = 'whitespace-pre-line'; t.textContent = teks;
        b.append(h, t);
        b.onclick = () => jawab(s.id, huruf);
        $('opsi').appendChild(b);
    });

    $('btn-prev').disabled = idx === 0;
    $('btn-next').disabled = idx === SOAL.length - 1;

    // Ubah $...$ menjadi rumus matematika
    window.renderMath($('q-text'));
    window.renderMath($('opsi'));

    renderGrid();
}

function renderGrid() {
    $('grid').innerHTML = '';
    SOAL.forEach((s, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = i + 1;
        const dijawab = !!JAWABAN[s.id];
        b.className = 'h-9 rounded text-sm border ' +
            (dijawab ? 'bg-blue-600 text-white border-blue-600 ' : 'bg-white hover:bg-slate-50 ') +
            (i === idx ? 'ring-2 ring-offset-1 ring-yellow-400' : '');
        b.onclick = () => { idx = i; render(); };
        $('grid').appendChild(b);
    });
}

async function jawab(soalId, huruf) {
    JAWABAN[soalId] = huruf;
    render();
    $('status-simpan').textContent = 'Menyimpan...';
    try {
        const r = await post(URL_JAWAB, { soal_id: soalId, jawaban: huruf });
        if (r.selesai) { selesai = true; location.href = r.redirect; return; }
        $('status-simpan').textContent = 'Tersimpan ✓';
    } catch (e) {
        $('status-simpan').textContent = '⚠ Gagal menyimpan, periksa koneksi lalu pilih ulang jawaban.';
    }
}

$('btn-prev').onclick = () => { if (idx > 0) { idx--; render(); } };
$('btn-next').onclick = () => { if (idx < SOAL.length - 1) { idx++; render(); } };

// ---------- Timer ----------
function tick() {
    const sisa = Math.max(0, Math.round((target - Date.now()) / 1000));
    const j = String(Math.floor(sisa / 3600)).padStart(2, '0');
    const m = String(Math.floor((sisa % 3600) / 60)).padStart(2, '0');
    const d = String(sisa % 60).padStart(2, '0');
    $('timer').textContent = `${j}:${m}:${d}`;
    $('timer').classList.toggle('text-red-400', sisa <= 300);
    if (sisa <= 0) kumpulkan();
}
setInterval(tick, 1000);
tick();

// ---------- Kumpulkan ----------
function kumpulkan() {
    if (selesai) return;
    selesai = true;
    if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
    $('form-selesai').submit();
}

$('btn-kumpul').onclick = () => {
    const belum = SOAL.filter((s) => !JAWABAN[s.id]).length;
    $('modal-info').textContent = belum > 0
        ? `Masih ada ${belum} soal yang belum dijawab. Yakin ingin mengumpulkan?`
        : 'Semua soal sudah dijawab. Kumpulkan sekarang?';
    $('modal-selesai').classList.remove('hidden');
};
$('btn-batal').onclick = () => $('modal-selesai').classList.add('hidden');
$('btn-yakin').onclick = kumpulkan;

// ---------- Fullscreen & anti-curang ----------
function tampilOverlay(judul, pesan) {
    $('overlay-judul').textContent = judul;
    $('overlay-pesan').textContent = pesan;
    $('btn-fs').textContent = 'Kembali ke Layar Penuh';
    $('overlay').classList.remove('hidden');
}

function toast(teks) {
    $('toast').textContent = teks;
    $('toast').classList.remove('hidden');
    setTimeout(() => $('toast').classList.add('hidden'), 4000);
}

$('btn-fs').onclick = async () => {
    try {
        await document.documentElement.requestFullscreen();
    } catch (e) {
        alert('Browser menolak mode layar penuh. Gunakan Chrome, Edge, atau Firefox versi terbaru.');
        return;
    }
    mulai = true;
    $('overlay').classList.add('hidden');
    render();
};

async function catatPelanggaran(alasan) {
    if (!mulai || selesai) return;

    const sekarang = Date.now();
    if (sekarang - lastViolation < 3000) return; // satu kejadian sering memicu beberapa event
    lastViolation = sekarang;

    try {
        const r = await post(URL_PELANGGARAN);
        if (r.selesai) { selesai = true; location.href = r.redirect; return; }
        pelanggaran = r.pelanggaran;
        $('pelanggaran').textContent = pelanggaran;
        const pesan = `Pelanggaran ${pelanggaran} dari ${BATAS}: ${alasan}. Ujian otomatis dikumpulkan pada pelanggaran ke-${BATAS}.`;
        $('overlay-warn').textContent = pesan;
        toast(pesan);
    } catch (e) { /* koneksi putus: abaikan, jangan menghentikan ujian */ }
}

document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && mulai && !selesai) {
        tampilOverlay('Anda keluar dari layar penuh', 'Kembali ke layar penuh untuk melanjutkan ujian. Waktu tetap berjalan.');
        catatPelanggaran('keluar dari layar penuh');
    }
});
document.addEventListener('visibilitychange', () => {
    if (document.hidden) catatPelanggaran('berpindah tab atau jendela');
});
window.addEventListener('blur', () => catatPelanggaran('jendela ujian kehilangan fokus'));

// Pencegahan dasar (tidak mutlak, hanya mempersulit)
['contextmenu', 'copy', 'cut', 'paste', 'dragstart'].forEach((ev) =>
    document.addEventListener(ev, (e) => e.preventDefault()));
document.addEventListener('keydown', (e) => {
    const k = e.key.toLowerCase();
    if (e.key === 'F12'
        || (e.ctrlKey && ['u', 's', 'p', 'c', 'v', 'x', 'a'].includes(k))
        || (e.ctrlKey && e.shiftKey && ['i', 'j', 'c'].includes(k))) {
        e.preventDefault();
    }
});
window.addEventListener('beforeunload', (e) => {
    if (!selesai) { e.preventDefault(); e.returnValue = ''; }
});

render();
</script>
</body>
</html>