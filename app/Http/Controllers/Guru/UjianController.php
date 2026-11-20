<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\{Kelas, Mapel, Ujian};
use Illuminate\Http\Request;

class UjianController extends Controller
{
    private function rules(): array
    {
        return [
            'judul'         => 'required|string|max:150',
            'mapel_id'      => 'required|exists:mapels,id',
            'kelas_ids'     => 'required|array|min:1',
            'kelas_ids.*'   => 'exists:kelas,id',
            'durasi_menit'  => 'required|integer|min:1|max:600',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
        ];
    }

    private function dataForm(): array
    {
        return [
            'mapels'    => Mapel::orderBy('nama')->get(),
            'kelasList' => Kelas::with('jurusan')->orderBy('tingkat')->orderBy('jurusan_id')->orderBy('kode')->get(),
        ];
    }

    public function index()
    {
        $ujians = auth()->user()->ujians()
            ->with(['mapel', 'kelas.jurusan'])
            ->withCount(['soals', 'hasils'])
            ->latest()->get();

        return view('guru.ujian.index', compact('ujians'));
    }

    public function create()
    {
        return view('guru.ujian.create', $this->dataForm());
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $ujian = Ujian::create([
            'guru_id'       => auth()->id(),
            'mapel_id'      => $data['mapel_id'],
            'judul'         => $data['judul'],
            'token'         => Ujian::buatToken(),
            'durasi_menit'  => $data['durasi_menit'],
            'waktu_mulai'   => $data['waktu_mulai'],
            'waktu_selesai' => $data['waktu_selesai'],
            'acak_soal'     => $request->boolean('acak_soal'),
            'aktif'         => true,
        ]);
        $ujian->kelas()->sync($data['kelas_ids']);

        return redirect()->route('guru.ujian.show', $ujian)
            ->with('success', 'Ujian dibuat. Sekarang tambahkan soalnya.');
    }

    public function show(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $ujian->load(['mapel', 'kelas.jurusan', 'soals']);

        return view('guru.ujian.show', compact('ujian'));
    }

    public function edit(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $ujian->load('kelas');

        return view('guru.ujian.edit', array_merge($this->dataForm(), compact('ujian')));
    }

    public function update(Request $request, Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $data = $request->validate($this->rules());

        $ujian->update([
            'mapel_id'      => $data['mapel_id'],
            'judul'         => $data['judul'],
            'durasi_menit'  => $data['durasi_menit'],
            'waktu_mulai'   => $data['waktu_mulai'],
            'waktu_selesai' => $data['waktu_selesai'],
            'acak_soal'     => $request->boolean('acak_soal'),
        ]);
        $ujian->kelas()->sync($data['kelas_ids']);

        return redirect()->route('guru.ujian.show', $ujian)->with('success', 'Ujian diperbarui.');
    }

    public function destroy(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $ujian->delete(); // soal, hasil, dan jawaban ikut terhapus (cascade)

        return redirect()->route('guru.ujian.index')->with('success', 'Ujian dihapus.');
    }

    public function resetToken(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $ujian->update(['token' => Ujian::buatToken()]);

        return back()->with('success', 'Token baru: ' . $ujian->token);
    }

    public function toggle(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $ujian->update(['aktif' => ! $ujian->aktif]);

        return back()->with('success', $ujian->aktif ? 'Ujian diaktifkan.' : 'Ujian dinonaktifkan.');
    }
}