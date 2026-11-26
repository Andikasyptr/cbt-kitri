<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\SoalController as AdminSoalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GambarSoalController;
use App\Http\Controllers\Guru;
use App\Http\Controllers\Guru\BankSoalController;
use App\Http\Controllers\Siswa;
use Illuminate\Support\Facades\Route;

// ===================== UMUM =====================
Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/gambar/soal/{soal}', [GambarSoalController::class, 'show'])
    ->middleware(['auth', 'akses'])->name('soal.gambar');

// ===================== ADMIN =====================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('jurusan', JurusanController::class)->only(['index', 'store', 'destroy']);
    Route::resource('kelas', KelasController::class)->only(['index', 'store', 'destroy'])
        ->parameters(['kelas' => 'kelas']);
    Route::resource('mapel', MapelController::class)->only(['index', 'store', 'destroy']);

    Route::get('guru', [UserController::class, 'index'])->defaults('role', 'guru')->name('guru.index');
    Route::post('guru', [UserController::class, 'store'])->defaults('role', 'guru')->name('guru.store');
    Route::get('guru/template', [UserController::class, 'template'])->defaults('role', 'guru')->name('guru.template');
    Route::post('guru/import', [UserController::class, 'import'])->defaults('role', 'guru')->name('guru.import');

    Route::get('siswa', [UserController::class, 'index'])->defaults('role', 'siswa')->name('siswa.index');
    Route::post('siswa', [UserController::class, 'store'])->defaults('role', 'siswa')->name('siswa.store');
    Route::get('siswa/template', [UserController::class, 'template'])->defaults('role', 'siswa')->name('siswa.template');
    Route::post('siswa/import', [UserController::class, 'import'])->defaults('role', 'siswa')->name('siswa.import');

    // Blokir akses siswa (SPP)
    Route::post('siswa/blokir-massal', [UserController::class, 'blokirMassal'])->name('siswa.blokir.massal');
    Route::post('siswa/blokir-import', [UserController::class, 'blokirImport'])->name('siswa.blokir.import');
    Route::post('siswa/{user}/blokir', [UserController::class, 'toggleBlokir'])->name('siswa.blokir');

    // Edit & hapus akun
    Route::put('akun/{user}', [UserController::class, 'update'])->name('akun.update');
    Route::delete('akun/{user}', [UserController::class, 'destroy'])->name('akun.destroy');

    Route::get('soal', [AdminSoalController::class, 'index'])->name('soal.index');
    Route::get('soal/{ujian}', [AdminSoalController::class, 'show'])->name('soal.show');
});

// ===================== GURU =====================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', [Guru\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('ujian', Guru\UjianController::class)->parameters(['ujian' => 'ujian']);
    Route::post('ujian/{ujian}/token', [Guru\UjianController::class, 'resetToken'])->name('ujian.token');
    Route::post('ujian/{ujian}/toggle', [Guru\UjianController::class, 'toggle'])->name('ujian.toggle');

    Route::post('ujian/{ujian}/soal', [Guru\SoalController::class, 'store'])->name('soal.store');
    Route::get('ujian/{ujian}/soal/template', [Guru\SoalController::class, 'template'])->name('soal.template');
    Route::post('ujian/{ujian}/soal/import', [Guru\SoalController::class, 'import'])->name('soal.import');
    Route::get('soal/{soal}/edit', [Guru\SoalController::class, 'edit'])->name('soal.edit');
    Route::put('soal/{soal}', [Guru\SoalController::class, 'update'])->name('soal.update');
    Route::delete('soal/{soal}', [Guru\SoalController::class, 'destroy'])->name('soal.destroy');

    Route::get('ujian/{ujian}/nilai', [Guru\NilaiController::class, 'index'])->name('nilai.index');
    Route::get('ujian/{ujian}/nilai/export', [Guru\NilaiController::class, 'export'])->name('nilai.export');
    Route::delete('hasil/{hasil}', [Guru\NilaiController::class, 'reset'])->name('nilai.reset');

    // Bank soal
    Route::get('bank', [BankSoalController::class, 'index'])->name('bank.index');
    Route::post('bank/tambah', [BankSoalController::class, 'tambah'])->name('bank.tambah');
    Route::post('bank/sinkron', [BankSoalController::class, 'sinkron'])->name('bank.sinkron');
    Route::get('bank/{bank}/gambar', [BankSoalController::class, 'gambar'])->name('bank.gambar');
    Route::delete('bank/{bank}', [BankSoalController::class, 'destroy'])->name('bank.destroy');
});

// ===================== SISWA =====================
Route::middleware(['auth', 'role:siswa', 'akses'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', [Siswa\DashboardController::class, 'index'])->name('dashboard');

    Route::post('token', [Siswa\UjianController::class, 'cekToken'])->name('token');
    Route::get('ujian/{ujian}/konfirmasi', [Siswa\UjianController::class, 'konfirmasi'])->name('ujian.konfirmasi');
    Route::post('ujian/{ujian}/mulai', [Siswa\UjianController::class, 'mulai'])->name('ujian.mulai');

    Route::get('kerjakan/{hasil}', [Siswa\UjianController::class, 'kerjakan'])->name('ujian.kerjakan');
    Route::post('kerjakan/{hasil}/jawab', [Siswa\UjianController::class, 'jawab'])->name('ujian.jawab');
    Route::post('kerjakan/{hasil}/pelanggaran', [Siswa\UjianController::class, 'pelanggaran'])->name('ujian.pelanggaran');
    Route::post('kerjakan/{hasil}/selesai', [Siswa\UjianController::class, 'selesai'])->name('ujian.selesai');
    Route::get('hasil/{hasil}', [Siswa\UjianController::class, 'hasil'])->name('ujian.hasil');
});