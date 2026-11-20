<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\{HasilUjian, Ujian, User};
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NilaiController extends Controller
{
    public function index(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);
        $ujian->load(['mapel', 'kelas.jurusan']);

        $hasils = $ujian->hasils()->with('siswa.kelas.jurusan')->get()
            ->sortBy(fn ($h) => $h->siswa->name)->values();

        $selesai = $hasils->whereNotNull('selesai_at');
        $stat = [
            'selesai'   => $selesai->count(),
            'rata'      => $selesai->count() ? round($selesai->avg('nilai'), 2) : 0,
            'tertinggi' => $selesai->max('nilai') ?? 0,
            'terendah'  => $selesai->min('nilai') ?? 0,
        ];

        // Siswa di kelas peserta yang belum mengerjakan
        $belum = User::siswa()
            ->whereIn('kelas_id', $ujian->kelas->pluck('id'))
            ->whereNotIn('id', $hasils->pluck('user_id'))
            ->with('kelas.jurusan')->orderBy('name')->get();

        return view('guru.nilai.index', compact('ujian', 'hasils', 'stat', 'belum'));
    }

    public function export(Ujian $ujian)
    {
        $this->pastikanMilikGuru($ujian->guru_id);

        $hasils = $ujian->hasils()->with('siswa.kelas.jurusan')->get()
            ->sortBy(fn ($h) => $h->siswa->name)->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai');
        $sheet->fromArray(['No', 'Nama', 'NIS', 'Kelas', 'Mulai', 'Selesai', 'Benar', 'Nilai', 'Pelanggaran'], null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        foreach ($hasils as $i => $h) {
            $r = $i + 2;
            $sheet->setCellValue("A$r", $i + 1);
            $sheet->setCellValue("B$r", $h->siswa->name);
            $sheet->setCellValueExplicit("C$r", (string) $h->siswa->nomor_induk, DataType::TYPE_STRING);
            $sheet->setCellValue("D$r", $h->siswa->kelas?->nama_lengkap ?? '-');
            $sheet->setCellValue("E$r", $h->mulai_at?->format('d/m/Y H:i'));
            $sheet->setCellValue("F$r", $h->selesai_at?->format('d/m/Y H:i') ?? 'Belum selesai');
            $sheet->setCellValue("G$r", $h->benar);
            $sheet->setCellValue("H$r", $h->nilai);
            $sheet->setCellValue("I$r", $h->pelanggaran);
        }
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'nilai-' . Str::slug($ujian->judul) . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Hapus hasil agar siswa bisa mengulang (misalnya listrik mati / salah klik)
    public function reset(HasilUjian $hasil)
    {
        $this->pastikanMilikGuru($hasil->ujian->guru_id);
        $hasil->delete(); // jawaban ikut terhapus

        return back()->with('success', 'Hasil siswa direset. Siswa dapat mengerjakan ulang.');
    }
}