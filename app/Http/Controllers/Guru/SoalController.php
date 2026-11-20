<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\{Soal, Ujian};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SoalController extends Controller
{
    private function rules(): array
    {
        return [
            'pertanyaan' => 'required|string',
            'opsi_a'     => 'required|string',
            'opsi_b'     => 'required|string',
            'opsi_c'     => 'required|string',
            'opsi_d'     => 'required|string',
            'opsi_e'     => 'required|string',
            'kunci'      => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
        ];
    }

    public function store(Request $request, Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $data = $request->validate($this->rules());

        $ujian->soals()->create($data);

        return back()->with('success', 'Soal ditambahkan.');
    }

    public function edit(Soal $soal)
    {
        $this->pastikanMilikGuru($soal->ujian->guru_id);

        return view('guru.soal.edit', compact('soal'));
    }

    public function update(Request $request, Soal $soal)
    {
        $this->pastikanMilikGuru($soal->ujian->guru_id);
        $soal->update($request->validate($this->rules()));

        return redirect()->route('guru.ujian.show', $soal->ujian_id)->with('success', 'Soal diperbarui.');
    }

    public function destroy(Soal $soal)
    {
        $this->pastikanMilikGuru($soal->ujian->guru_id);

        if ($soal->ujian->hasils()->exists()) {
            return back()->withErrors(['soal' => 'Ujian sudah dikerjakan siswa, soal tidak bisa dihapus.']);
        }

        $soal->delete();

        return back()->with('success', 'Soal dihapus.');
    }

    // ---------- Import Excel ----------
    public function import(Request $request, Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);

        $hapusLama = $request->boolean('hapus_lama');
        if ($hapusLama && $ujian->hasils()->exists()) {
            return back()->withErrors(['file' => 'Ujian sudah dikerjakan siswa, soal lama tidak bisa dihapus.']);
        }

        $rows = IOFactory::load($request->file('file')->getRealPath())
            ->getActiveSheet()->toArray(null, true, true, false);

        array_shift($rows); // buang baris header

        $siap = [];
        $dilewati = [];

        foreach ($rows as $i => $r) {
            $baris = $i + 2; // nomor baris di Excel
            $r = array_map(fn ($v) => trim((string) $v), array_slice(array_pad($r, 7, ''), 0, 7));

            if (implode('', $r) === '') {
                continue; // baris kosong
            }

            [$tanya, $a, $b, $c, $d, $e, $kunci] = $r;
            $kunci = strtoupper($kunci);

            if ($tanya === '' || in_array('', [$a, $b, $c, $d, $e], true)
                || ! in_array($kunci, ['A', 'B', 'C', 'D', 'E'], true)) {
                $dilewati[] = $baris;
                continue;
            }

            $siap[] = [
                'ujian_id'   => $ujian->id,
                'pertanyaan' => $tanya,
                'opsi_a' => $a, 'opsi_b' => $b, 'opsi_c' => $c, 'opsi_d' => $d, 'opsi_e' => $e,
                'kunci'      => $kunci,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($siap)) {
            return back()->withErrors(['file' => 'Tidak ada soal valid di file. Pastikan format sesuai template.']);
        }

        DB::transaction(function () use ($ujian, $siap, $hapusLama) {
            if ($hapusLama) {
                $ujian->soals()->delete();
            }
            Soal::insert($siap);
        });

        $pesan = count($siap) . ' soal berhasil diimport.';
        if ($dilewati) {
            $pesan .= ' Baris dilewati karena tidak lengkap/kunci salah: ' . implode(', ', $dilewati) . '.';
        }

        return back()->with('success', $pesan);
    }

    // ---------- Template Excel ----------
    public function template(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Soal');

        $sheet->fromArray(['Pertanyaan', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'Kunci (A-E)'], null, 'A1');
        $sheet->fromArray([
            ['Ibu kota negara Indonesia adalah ...', 'Surabaya', 'Bandung', 'Jakarta', 'Medan', 'Makassar', 'C'],
            ['Hasil dari 5 + 3 adalah ...', '6', '7', '8', '9', '10', 'C'],
        ], null, 'A2');

        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
        $sheet->getColumnDimension('A')->setWidth(60);
        foreach (['B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(25);
        }
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getStyle('A2:G200')->getAlignment()->setWrapText(true);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template-soal-pilihan-ganda.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}