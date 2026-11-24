<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\{BankSoal, HasilUjian, Ujian, User};
use Illuminate\Http\Request;

class UjianController extends Controller
{
    private const BATAS_PELANGGARAN = 2;

    private function milikSiswa(HasilUjian $hasil): void
    {
        abort_unless((int) $hasil->user_id === (int) auth()->id(), 403);
    }

    // Kembalikan pesan error, atau null jika siswa boleh mengerjakan
    private function validasi(Ujian $ujian, User $user): ?string
    {
        if (! $ujian->kelas()->where('kelas.id', $user->kelas_id)->exists()) {
            return 'Ujian ini bukan untuk kelas Anda.';
        }
        if (! $ujian->aktif) {
            return 'Ujian sedang dinonaktifkan oleh guru.';
        }
        if (now()->lt($ujian->waktu_mulai)) {
            return 'Ujian belum dibuka. Jadwal mulai: ' . $ujian->waktu_mulai->translatedFormat('d M Y H:i') . '.';
        }
        if (now()->gt($ujian->waktu_selesai)) {
            return 'Waktu ujian sudah ditutup.';
        }
        if ($ujian->soals()->count() === 0) {
            return 'Ujian belum memiliki soal.';
        }

        $hasil = HasilUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();
        if ($hasil && $hasil->sudahSelesai()) {
            return 'Anda sudah mengerjakan ujian ini.';
        }

        return null;
    }

    // ---------- Input token ----------
    public function cekToken(Request $request)
    {
        $request->validate(['token' => 'required|string|max:10']);
        $user = auth()->user();

        $ujian = Ujian::where('token', strtoupper(trim($request->token)))->first();
        if (! $ujian) {
            return back()->withErrors(['token' => 'Token tidak ditemukan. Periksa kembali.'])->withInput();
        }

        // Sudah pernah mulai tapi belum selesai -> langsung lanjutkan
        $hasil = HasilUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();
        if ($hasil && ! $hasil->sudahSelesai() && $ujian->kelas()->where('kelas.id', $user->kelas_id)->exists()) {
            return redirect()->route('siswa.ujian.kerjakan', $hasil);
        }

        if ($pesan = $this->validasi($ujian, $user)) {
            return back()->withErrors(['token' => $pesan])->withInput();
        }

        session()->put("token_ok.{$ujian->id}", true);

        return redirect()->route('siswa.ujian.konfirmasi', $ujian);
    }

    public function konfirmasi(Ujian $ujian)
    {
        abort_unless(session("token_ok.{$ujian->id}"), 403, 'Masukkan token ujian terlebih dahulu.');

        if ($pesan = $this->validasi($ujian, auth()->user())) {
            return redirect()->route('siswa.dashboard')->withErrors(['token' => $pesan]);
        }

        $ujian->load('mapel')->loadCount('soals');

        return view('siswa.konfirmasi', [
            'ujian' => $ujian,
            'batas' => self::BATAS_PELANGGARAN,
        ]);
    }

    public function mulai(Ujian $ujian)
    {
        $user = auth()->user();

        if ($pesan = $this->validasi($ujian, $user)) {
            return redirect()->route('siswa.dashboard')->withErrors(['token' => $pesan]);
        }

        $hasil = HasilUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();

        if (! $hasil) {
            abort_unless(session("token_ok.{$ujian->id}"), 403, 'Masukkan token ujian terlebih dahulu.');

            $hasil = HasilUjian::create([
                'ujian_id'    => $ujian->id,
                'user_id'     => $user->id,
                'mulai_at'    => now(),
                'benar'       => 0,
                'pelanggaran' => 0,
            ]);

            // Salin soal ujian ini ke bank soal guru (hanya sekali)
            $this->arsipkan($ujian);
        }

        session()->forget("token_ok.{$ujian->id}");

        return redirect()->route('siswa.ujian.kerjakan', $hasil);
    }

    // Hanya siswa PERTAMA yang memicu penyalinan ke bank (update atomik), sisanya dilewati
    private function arsipkan(Ujian $ujian): void
    {
        try {
            $pertama = Ujian::whereKey($ujian->id)->whereNull('diarsipkan_at')->update(['diarsipkan_at' => now()]);
            if ($pertama === 1) {
                BankSoal::arsipkanUjian($ujian);
            }
        } catch (\Throwable $e) {
            report($e); // kegagalan arsip tidak boleh mengganggu ujian siswa
        }
    }

    // ---------- Halaman ujian ----------
    public function kerjakan(HasilUjian $hasil)
    {
        $this->milikSiswa($hasil);

        if ($hasil->sudahSelesai()) {
            return redirect()->route('siswa.ujian.hasil', $hasil);
        }
        if ($hasil->sisaDetik() <= 0) {
            $hasil->selesaikan();

            return redirect()->route('siswa.ujian.hasil', $hasil);
        }

        $ujian = $hasil->ujian()->with('mapel')->first();

        // Hanya kolom yang dibutuhkan. Kunci jawaban TIDAK dikirim ke browser.
        $soals = $ujian->soals()
            ->select(['id', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'updated_at'])
            ->withExists('gambar')
            ->get();

        // Urutan acak yang tetap sama untuk siswa ini (aman saat halaman di-refresh)
        if ($ujian->acak_soal) {
            $soals = $soals->sortBy(fn ($s) => crc32($hasil->id . '-' . $s->id));
        }

        $soalData = $soals->values()->map(fn ($s) => [
            'id'         => $s->id,
            'pertanyaan' => $s->pertanyaan,
            'opsi'       => $s->opsi,
            'gambar'     => $s->gambar_exists
                ? route('soal.gambar', $s) . '?v=' . $s->updated_at->timestamp
                : null,
        ])->all();

        return view('siswa.kerjakan', [
            'hasil'    => $hasil,
            'ujian'    => $ujian,
            'soalData' => $soalData,
            'jawaban'  => $hasil->jawabans()->pluck('jawaban', 'soal_id'),
            'sisa'     => $hasil->sisaDetik(),
            'batas'    => self::BATAS_PELANGGARAN,
        ]);
    }

    // Simpan jawaban otomatis setiap siswa memilih opsi (AJAX)
    public function jawab(Request $request, HasilUjian $hasil)
    {
        $this->milikSiswa($hasil);

        if ($hasil->sudahSelesai() || $hasil->sisaDetik() <= 0) {
            $hasil->selesaikan();

            return response()->json(['selesai' => true, 'redirect' => route('siswa.ujian.hasil', $hasil)]);
        }

        $data = $request->validate([
            'soal_id' => 'required|integer',
            'jawaban' => 'required|in:A,B,C,D,E',
        ]);

        abort_unless($hasil->ujian->soals()->whereKey($data['soal_id'])->exists(), 422);

        $hasil->jawabans()->updateOrCreate(
            ['soal_id' => $data['soal_id']],
            ['jawaban' => $data['jawaban']]
        );

        return response()->json(['ok' => true]);
    }

    // Catat pelanggaran (keluar fullscreen, pindah tab, dll)
    public function pelanggaran(HasilUjian $hasil)
    {
        $this->milikSiswa($hasil);

        if ($hasil->sudahSelesai()) {
            return response()->json(['selesai' => true, 'redirect' => route('siswa.ujian.hasil', $hasil)]);
        }

        $hasil->increment('pelanggaran');

        if ($hasil->pelanggaran >= self::BATAS_PELANGGARAN) {
            $hasil->selesaikan();

            return response()->json(['selesai' => true, 'redirect' => route('siswa.ujian.hasil', $hasil)]);
        }

        return response()->json(['selesai' => false, 'pelanggaran' => $hasil->pelanggaran]);
    }

    public function selesai(HasilUjian $hasil)
    {
        $this->milikSiswa($hasil);
        $hasil->selesaikan();

        return redirect()->route('siswa.ujian.hasil', $hasil);
    }

    public function hasil(HasilUjian $hasil)
    {
        $this->milikSiswa($hasil);

        if (! $hasil->sudahSelesai()) {
            return redirect()->route('siswa.ujian.kerjakan', $hasil);
        }

        $hasil->load('ujian.mapel');

        return view('siswa.hasil', [
            'hasil' => $hasil,
            'total' => $hasil->ujian->soals()->count(),
            'batas' => self::BATAS_PELANGGARAN,
        ]);
    }
}