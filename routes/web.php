<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : Inertia\Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Direktori & Buku Induk Siswa (Admin & Tata Usaha)
    Route::resource('siswa', SiswaController::class)->except(['create', 'edit', 'show']);
    Route::get('/siswa/{siswa}/buku-induk', [SiswaController::class, 'showBukuInduk'])->name('siswa.buku-induk');
    Route::post('/siswa/{siswa}/nilai-semester', [SiswaController::class, 'updateNilaiSemester'])->name('siswa.nilai-semester.update');

    // Import Massal & ZIP Pasfoto (Admin & Tata Usaha)
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::get('/template-identitas', [ImportController::class, 'downloadTemplateIdentitas'])->name('template-identitas');
        Route::get('/template-nilai', [ImportController::class, 'downloadTemplateNilai'])->name('template-nilai');
        Route::post('/identitas', [ImportController::class, 'importIdentitas'])->name('identitas');
        Route::post('/nilai', [ImportController::class, 'importNilai'])->name('nilai');
        Route::post('/zip-foto', [ImportController::class, 'uploadZipFoto'])->name('zip-foto');
    });

    // Khusus Role Admin: Data Master & Manajemen User
    Route::middleware(['role:admin'])->group(function () {
        // Master Data
        Route::prefix('master-data')->name('master.')->group(function () {
            Route::get('/', [MasterDataController::class, 'index'])->name('index');

            // Jurusan (Konsentrasi Keahlian)
            Route::post('/jurusan', [MasterDataController::class, 'storeJurusan'])->name('jurusan.store');
            Route::put('/jurusan/{jurusan}', [MasterDataController::class, 'updateJurusan'])->name('jurusan.update');
            Route::delete('/jurusan/{jurusan}', [MasterDataController::class, 'destroyJurusan'])->name('jurusan.destroy');

            // Mata Pelajaran
            Route::post('/mapel', [MasterDataController::class, 'storeMapel'])->name('mapel.store');
            Route::put('/mapel/{mapel}', [MasterDataController::class, 'updateMapel'])->name('mapel.update');
            Route::delete('/mapel/{mapel}', [MasterDataController::class, 'destroyMapel'])->name('mapel.destroy');

            // Ekstrakurikuler
            Route::post('/ekskul', [MasterDataController::class, 'storeEkskul'])->name('ekskul.store');
            Route::put('/ekskul/{ekskul}', [MasterDataController::class, 'updateEkskul'])->name('ekskul.update');
            Route::delete('/ekskul/{ekskul}', [MasterDataController::class, 'destroyEkskul'])->name('ekskul.destroy');

            // Tahun Pelajaran
            Route::post('/tahun', [MasterDataController::class, 'storeTahun'])->name('tahun.store');
            Route::put('/tahun/{tahun}', [MasterDataController::class, 'updateTahun'])->name('tahun.update');
            Route::delete('/tahun/{tahun}', [MasterDataController::class, 'destroyTahun'])->name('tahun.destroy');
            Route::patch('/tahun/{tahun}/aktif', [MasterDataController::class, 'setTahunAktif'])->name('tahun.aktif');
        });

        // Manajemen User
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/settings.php';
