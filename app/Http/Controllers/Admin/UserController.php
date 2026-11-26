<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Kelas, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserController extends Controller
{
    public function index(Request $request, string $role)
    {
        $perHalaman = in_array((int) $request->per_page, [10, 25, 50, 100, 250, 500])
            ? (int) $request->per_page
            : 10;

        $users = User::with('kelas.jurusan')
            ->where('role', $role)
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$s%")
                ->orWhere('username', 'like', "%$s%")
                ->orWhere('nomor_induk', 'like', "%$s%")))
            ->when($role === 'siswa' && $request->kelas_id, fn ($q, $k) => $q->where('kelas_id', $k))
            ->when($role === 'siswa' && $request->status === 'blokir', fn ($q) => $q->where('diblokir', true))
            ->when($role === 'siswa' && $request->status === 'aktif', fn ($q) => $q->where(fn ($w) => $w->where('diblokir', false)->orWhereNull('diblokir')))
            ->orderBy('name')
            ->paginate($perHalaman)
            ->withQueryString();

        return view('admin.users.index', [
            'role'        => $role,
            'users'       => $users,
            'total'       => User::where('role', $role)->count(),
            'totalBlokir' => $role === 'siswa' ? User::where('role', 'siswa')->where('diblokir', true)->count() : 0,
            'kelasList'   => $role === 'siswa'
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

    // Edit data akun guru/siswa. Password hanya diganti jika diisi.
    public function update(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['guru', 'siswa']), 403, 'Akun admin tidak diedit di sini.');

        $siswa = $user->role === 'siswa';

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username,' . $user->id,
            'nomor_induk' => 'nullable|string|max:30',
            'password'    => 'nullable|string|min:6',
            'kelas_id'    => $siswa ? 'required|exists:kelas,id' : 'nullable',
        ]);

        $data['username'] = strtolower(preg_replace('/\s+/', '', $data['username']));

        if (empty($data['password'])) {
            unset($data['password']); // password lama dipertahankan
        }
        if (! $siswa) {
            $data['kelas_id'] = null;
        }

        $user->update($data);

        return back()->with('success', 'Data ' . $user->role . ' diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->role === 'admin', 403, 'Akun admin tidak boleh dihapus.');
        $user->delete();

        return back()->with('success', 'Akun dihapus.');
    }

    // ================= BLOKIR AKSES SISWA =================

    public function toggleBlokir(Request $request, User $user)
    {
        abort_unless($user->role === 'siswa', 403);

        if ($user->diblokir) {
            $this->setBlokir(User::whereKey($user->id), false, null);

            return back()->with('success', "Akses {$user->name} dibuka.");
        }

        $alasan = trim((string) $request->input('alasan')) ?: 'Belum membayar SPP';
        $this->setBlokir(User::whereKey($user->id), true, $alasan);

        return back()->with('success', "Akses {$user->name} diblokir.");
    }

    public function blokirMassal(Request $request)
    {
        $data = $request->validate([
            'mode'     => 'required|in:pilihan,kelas',
            'aksi'     => 'required|in:blokir,buka',
            'alasan'   => 'nullable|string|max:150',
            'ids'      => 'nullable|array',
            'ids.*'    => 'integer',
            'kelas_id' => 'nullable|string',
        ]);

        $query = User::where('role', 'siswa');

        if ($data['mode'] === 'pilihan') {
            if (empty($data['ids'])) {
                return back()->withErrors(['blokir' => 'Centang minimal satu siswa terlebih dahulu.']);
            }
            $query->whereIn('id', $data['ids']);
        } else {
            if (empty($data['kelas_id'])) {
                return back()->withErrors(['blokir' => 'Pilih kelas terlebih dahulu.']);
            }
            if ($data['kelas_id'] === 'semua') {
                if ($data['aksi'] === 'blokir') {
                    return back()->withErrors(['blokir' => 'Memblokir semua siswa sekaligus tidak diizinkan. Pilih kelas tertentu.']);
                }
            } else {
                $query->where('kelas_id', (int) $data['kelas_id']);
            }
        }

        $blokir = $data['aksi'] === 'blokir';
        $alasan = $blokir ? (trim((string) ($data['alasan'] ?? '')) ?: 'Belum membayar SPP') : null;
        $n = $this->setBlokir($query, $blokir, $alasan);

        return back()->with('success', $n . ' siswa ' . ($blokir ? 'diblokir.' : 'dibuka aksesnya.'));
    }

    public function blokirImport(Request $request)
    {
        $data = $request->validate([
            'file'   => 'required|file|mimes:xlsx,xls|max:5120',
            'aksi'   => 'required|in:blokir,buka',
            'alasan' => 'nullable|string|max:150',
        ]);

        $rows = IOFactory::load($request->file('file')->getRealPath())
            ->getSheet(0)->toArray(null, true, true, false);
        array_shift($rows);

        $nis = collect($rows)->map(fn ($r) => trim((string) ($r[0] ?? '')))->filter()->unique()->values();
        if ($nis->isEmpty()) {
            return back()->withErrors(['blokir' => 'Tidak ada NIS di kolom A file Excel.']);
        }

        $cocok = User::where('role', 'siswa')->whereIn('nomor_induk', $nis)->pluck('nomor_induk');
        $tidakAda = $nis->diff($cocok)->values();

        $blokir = $data['aksi'] === 'blokir';
        $alasan = $blokir ? (trim((string) ($data['alasan'] ?? '')) ?: 'Belum membayar SPP') : null;
        $n = $this->setBlokir(User::where('role', 'siswa')->whereIn('nomor_induk', $nis), $blokir, $alasan);

        $res = back()->with('success', $n . ' siswa ' . ($blokir ? 'diblokir.' : 'dibuka aksesnya.'));

        if ($tidakAda->isNotEmpty()) {
            $res->withErrors(['blokir' => 'NIS tidak ditemukan (' . $tidakAda->count() . '): ' . $tidakAda->take(15)->implode(', ') . ($tidakAda->count() > 15 ? ', ...' : '')]);
        }

        return $res;
    }

    private function setBlokir($query, bool $blokir, ?string $alasan): int
    {
        return $query->update([
            'diblokir'      => $blokir,
            'alasan_blokir' => $blokir ? $alasan : null,
            'diblokir_at'   => $blokir ? now() : null,
        ]);
    }

    // ================= TEMPLATE & IMPORT AKUN =================

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

            $ref = $spreadsheet->createSheet();
            $ref->setTitle('Daftar Kelas');
            $ref->setCellValue('A1', 'Nama kelas yang valid (salin persis ke kolom Kelas)');
            $ref->getStyle('A1')->getFont()->setBold(true);
            foreach ($kelas as $i => $nama) {
                $ref->setCellValue('A' . ($i + 2), $nama);
            }
            $ref->getColumnDimension('A')->setWidth(55);

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

    public function import(Request $request, string $role)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);
        set_time_limit(300);

        $siswa = $role === 'siswa';
        $rows = IOFactory::load($request->file('file')->getRealPath())
            ->getSheet(0)->toArray(null, true, true, false);
        array_shift($rows);

        $kelasMap = $siswa ? $this->petaKelas() : [];
        $dipakai = User::pluck('username')->map(fn ($u) => strtolower($u))->flip()->all();

        $siap = [];
        $gagal = [];

        foreach ($rows as $i => $r) {
            $baris = $i + 2;
            $r = array_map(fn ($v) => trim((string) $v), array_pad($r, $siswa ? 5 : 4, ''));

            if (implode('', $r) === '') {
                continue;
            }

            if ($siswa) {
                [$nama, $induk, $kelasTxt, $username, $password] = $r;
            } else {
                [$nama, $induk, $username, $password] = $r;
                $kelasTxt = '';
            }

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

            $dipakai[$username] = true;
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
                User::create($data);
            }
        });

        $pesan = count($siap) . ' akun ' . $role . ' berhasil diimport.';
        if ($gagal) {
            $pesan .= ' ' . count($gagal) . ' baris dilewati.';
        }

        return back()->with('success', $pesan)->withErrors($this->detailGagal($gagal));
    }

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