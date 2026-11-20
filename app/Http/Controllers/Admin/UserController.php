<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Kelas, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserController extends Controller
{
    public function index(Request $request, string $role)
    {
        $users = User::with('kelas.jurusan')
            ->where('role', $role)
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$s%")
                ->orWhere('username', 'like', "%$s%")
                ->orWhere('nomor_induk', 'like', "%$s%")))
            ->when($role === 'siswa' && $request->kelas_id, fn ($q, $k) => $q->where('kelas_id', $k))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'role'      => $role,
            'users'     => $users,
            'total'     => User::where('role', $role)->count(),
            'kelasList' => $role === 'siswa'
                ? Kelas::with('jurusan')->orderBy('tingkat')->orderBy('jurusan_id')->orderBy('kode')->get()
                : collect(),
        ]);
    }

    public function store(Request $request, string $role)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username',
            'nomor_induk' => 'nullable|string|max:30',
            'password'    => 'required|string|min:6',
            'kelas_id'    => $role === 'siswa' ? 'required|exists:kelas,id' : 'nullable',
        ]);

        $data['role'] = $role;
        if ($role !== 'siswa') {
            $data['kelas_id'] = null;
        }

        User::create($data);

        return back()->with('success', ucfirst($role) . ' berhasil didaftarkan.');
    }

    public function destroy(User $user)
    {
        abort_if($user->role === 'admin', 403, 'Akun admin tidak boleh dihapus.');
        $user->delete();

        return back()->with('success', 'Akun dihapus.');
    }

    // ---------- Template Excel ----------
    public function template(string $role)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $siswa = $role === 'siswa';

        $sheet->setTitle($siswa ? 'Siswa' : 'Guru');
        $header = $siswa
            ? ['Nama Lengkap', 'NIS', 'Kelas', 'Username', 'Password']
            : ['Nama Lengkap', 'NIP', 'Username', 'Password'];
        $lastCol = $siswa ? 'E' : 'D';

        $sheet->fromArray($header, null, 'A1');
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$lastCol}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');

        // Kolom nomor induk berformat teks agar angka 0 di depan tidak hilang
        $sheet->getStyle('B2:B1000')->getNumberFormat()->setFormatCode('@');

        if ($siswa) {
            $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('jurusan_id')->orderBy('kode')->get()
                ->map->nama_lengkap->values();
            $contohKelas = $kelas->first() ?? 'XII TKJ A';

            $sample = [
                ['Budi Santoso', '2024001', $contohKelas, 'budi.santoso', 'rahasia123'],
                ['Siti Aisyah', '2024002', $contohKelas, 'siti.aisyah', 'rahasia123'],
            ];
            foreach ($sample as $i => $row) {
                foreach ($row as $c => $val) {
                    $sheet->setCellValueExplicit([$c + 1, $i + 2], (string) $val, DataType::TYPE_STRING);
                }
            }

            // Sheet 2: daftar kelas yang valid
            $ref = $spreadsheet->createSheet();
            $ref->setTitle('Daftar Kelas');
            $ref->setCellValue('A1', 'Nama kelas yang valid (salin persis ke kolom Kelas)');
            $ref->getStyle('A1')->getFont()->setBold(true);
            foreach ($kelas as $i => $nama) {
                $ref->setCellValue('A' . ($i + 2), $nama);
            }
            $ref->getColumnDimension('A')->setWidth(55);

            // Dropdown kelas pada kolom C
            if ($kelas->count() > 0) {
                $v = $sheet->getCell('C2')->getDataValidation();
                $v->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowDropDown(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Kelas tidak valid')
                    ->setError('Pilih kelas dari daftar.')
                    ->setFormula1("'Daftar Kelas'!\$A\$2:\$A\$" . ($kelas->count() + 1));
                for ($r = 3; $r <= 500; $r++) {
                    $sheet->getCell("C$r")->setDataValidation(clone $v);
                }
            }

            $spreadsheet->setActiveSheetIndex(0);
            foreach (['A' => 30, 'B' => 16, 'C' => 20, 'D' => 22, 'E' => 18] as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
        } else {
            $sample = [
                ['Ahmad Fauzi, S.Pd', '198001012005011001', 'ahmad.fauzi', 'rahasia123'],
                ['Dewi Lestari, S.Kom', '198505052010012002', 'dewi.lestari', 'rahasia123'],
            ];
            foreach ($sample as $i => $row) {
                foreach ($row as $c => $val) {
                    $sheet->setCellValueExplicit([$c + 1, $i + 2], (string) $val, DataType::TYPE_STRING);
                }
            }
            foreach (['A' => 30, 'B' => 22, 'C' => 22, 'D' => 18] as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, "template-akun-{$role}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ---------- Import Excel ----------
    public function import(Request $request, string $role)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);
        set_time_limit(300); // hashing password ratusan akun butuh waktu

        $siswa = $role === 'siswa';
        $rows = IOFactory::load($request->file('file')->getRealPath())
            ->getSheet(0)->toArray(null, true, true, false);
        array_shift($rows); // buang header

        $kelasMap = $siswa ? $this->petaKelas() : [];
        $dipakai = User::pluck('username')->map(fn ($u) => strtolower($u))->flip()->all();

        $siap = [];
        $gagal = [];

        foreach ($rows as $i => $r) {
            $baris = $i + 2;
            $r = array_map(fn ($v) => trim((string) $v), array_pad($r, $siswa ? 5 : 4, ''));

            if (implode('', $r) === '') {
                continue; // baris kosong
            }

            if ($siswa) {
                [$nama, $induk, $kelasTxt, $username, $password] = $r;
            } else {
                [$nama, $induk, $username, $password] = $r;
                $kelasTxt = '';
            }

            // Kosong -> pakai nomor induk
            $username = strtolower(preg_replace('/\s+/', '', $username !== '' ? $username : $induk));
            $password = $password !== '' ? $password : $induk;

            if ($nama === '') {
                $gagal[] = "Baris $baris: nama kosong";
                continue;
            }
            if ($username === '') {
                $gagal[] = "Baris $baris: username dan nomor induk kosong";
                continue;
            }
            if (strlen($password) < 6) {
                $gagal[] = "Baris $baris: password minimal 6 karakter";
                continue;
            }
            if (isset($dipakai[$username])) {
                $gagal[] = "Baris $baris: username \"$username\" sudah dipakai";
                continue;
            }

            $kelasId = null;
            if ($siswa) {
                $kelasId = $kelasMap[$this->norm($kelasTxt)] ?? null;
                if (! $kelasId) {
                    $gagal[] = "Baris $baris: kelas \"$kelasTxt\" tidak ditemukan";
                    continue;
                }
            }

            $dipakai[$username] = true; // cegah username ganda di dalam file
            $siap[] = [
                'name'        => $nama,
                'username'    => $username,
                'nomor_induk' => $induk !== '' ? $induk : null,
                'role'        => $role,
                'kelas_id'    => $kelasId,
                'password'    => $password,
            ];
        }

        if (empty($siap)) {
            return back()->withErrors(['import' => 'Tidak ada data valid yang bisa diimport.'] + $this->detailGagal($gagal));
        }

        DB::transaction(function () use ($siap) {
            foreach ($siap as $data) {
                User::create($data); // password di-hash otomatis oleh cast model
            }
        });

        $pesan = count($siap) . ' akun ' . $role . ' berhasil diimport.';
        if ($gagal) {
            $pesan .= ' ' . count($gagal) . ' baris dilewati.';
        }

        return back()->with('success', $pesan)->withErrors($this->detailGagal($gagal));
    }

    // Normalisasi nama kelas: "XII TKJ A" == "xii-tkj-a" == "12 TKJ A"
    private function norm(string $s): string
    {
        return strtoupper(preg_replace('/[\s\-_.]+/', '', $s));
    }

    private function petaKelas(): array
    {
        $peta = [];
        foreach (Kelas::with('jurusan')->get() as $k) {
            $peta[$this->norm($k->nama_lengkap)] = $k->id;
            $peta[$this->norm($k->tingkat . ' ' . ($k->jurusan->singkatan ?? '') . ' ' . $k->kode)] = $k->id;
        }

        return $peta;
    }

    // Tampilkan maksimal 15 alasan agar pesan tidak terlalu panjang
    private function detailGagal(array $gagal): array
    {
        $out = [];
        foreach (array_slice($gagal, 0, 15) as $i => $g) {
            $out["gagal$i"] = $g;
        }
        if (count($gagal) > 15) {
            $out['gagal_lain'] = '... dan ' . (count($gagal) - 15) . ' baris lainnya.';
        }

        return $out;
    }
}