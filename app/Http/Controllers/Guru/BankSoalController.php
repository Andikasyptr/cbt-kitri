<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\{BankSoal, Mapel, Ujian};
use Illuminate\Http\Request;

class BankSoalController extends Controller
{
    private function milik(BankSoal $bank): void
    {
        abort_unless((int) $bank->guru_id === (int) auth()->id(), 403);
    }

    public function index(Request $request)
    {
        $soals = BankSoal::where('guru_id', auth()->id())
            ->select([
                'id', 'guru_id', 'mapel_id', 'sumber', 'pertanyaan',
                'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'kunci',
                'ada_gambar', 'dipakai', 'created_at', 'updated_at',
            ]) // gambar_data sengaja tidak dimuat
            ->with('mapel')
            ->when($request->mapel_id, fn ($q, $m) => $q->where('mapel_id', $m))
            ->when($request->q, fn ($q, $s) => $q->where('pertanyaan', 'like', "%$s%"))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('guru.bank.index', [
            'soals'  => $soals,
            'total'  => BankSoal::where('guru_id', auth()->id())->count(),
            'mapels' => Mapel::orderBy('nama')->get(['id', 'nama']),
            // hanya ujian yang belum dikerjakan siswa
            'ujians' => auth()->user()->ujians()->whereDoesntHave('hasils')->latest()->get(['id', 'judul']),
            'target' => (int) $request->ujian,
        ]);
    }

    // Salin soal terpilih ke sebuah ujian
    public function tambah(Request $request)
    {
        $data = $request->validate([
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer',
            'ujian_id' => 'required|exists:ujians,id',
        ], [
            'ids.required'      => 'Centang minimal satu soal.',
            'ujian_id.required' => 'Pilih ujian tujuan.',
        ]);

        $ujian = Ujian::findOrFail($data['ujian_id']);
        $this->pastikanMilikGuru($ujian->guru_id);

        if ($ujian->hasils()->exists()) {
            return back()->withErrors(['ids' => 'Ujian tujuan sudah dikerjakan siswa, soal tidak bisa ditambah.']);
        }

        $ada = $ujian->soals()->get()->map(fn ($s) => BankSoal::hitung(
            $s->pertanyaan, $s->opsi_a, $s->opsi_b, $s->opsi_c, $s->opsi_d, $s->opsi_e, $s->kunci
        ))->flip();

        $masuk = 0;
        $lewat = 0;

        foreach (BankSoal::where('guru_id', auth()->id())->whereIn('id', $data['ids'])->get() as $b) {
            if (isset($ada[$b->hash])) {
                $lewat++;
                continue;
            }

            $soal = $ujian->soals()->create([
                'pertanyaan' => $b->pertanyaan,
                'opsi_a' => $b->opsi_a, 'opsi_b' => $b->opsi_b, 'opsi_c' => $b->opsi_c,
                'opsi_d' => $b->opsi_d, 'opsi_e' => $b->opsi_e,
                'kunci'      => $b->kunci,
            ]);

            if ($b->ada_gambar && $b->gambar_data) {
                $soal->gambar()->create(['mime' => $b->gambar_mime, 'data' => $b->gambar_data]);
            }

            $b->increment('dipakai');
            $ada[$b->hash] = true;
            $masuk++;
        }

        $pesan = "$masuk soal ditambahkan ke ujian.";
        if ($lewat) {
            $pesan .= " $lewat soal dilewati karena sudah ada di ujian itu.";
        }

        return redirect()->route('guru.ujian.show', $ujian)->with('success', $pesan);
    }

    // Salin soal dari ujian yang sudah pernah dikerjakan (sebelum fitur ini ada)
    public function sinkron()
    {
        $baru = 0;

        $ujians = auth()->user()->ujians()->whereHas('hasils')->get();
        foreach ($ujians as $u) {
            $baru += BankSoal::arsipkanUjian($u);
            $u->update(['diarsipkan_at' => now()]);
        }

        return back()->with('success', "Sinkronisasi selesai: $baru soal baru masuk ke bank dari {$ujians->count()} ujian.");
    }

    public function gambar(BankSoal $bank)
    {
        $this->milik($bank);
        abort_if(! $bank->ada_gambar || ! $bank->gambar_data, 404);

        return response(base64_decode($bank->gambar_data), 200, [
            'Content-Type'           => $bank->gambar_mime ?: 'image/jpeg',
            'Cache-Control'          => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(BankSoal $bank)
    {
        $this->milik($bank);
        $bank->delete();

        return back()->with('success', 'Soal dihapus dari bank. Ujian yang sudah memakainya tidak terpengaruh.');
    }
}