<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CadanganController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanggananController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ResetSandiController;
use App\Http\Controllers\Admin\ServisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LupaSandiController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// Publik: cek status perbaikan
Route::get('/', [StatusController::class, 'index'])->name('home');
Route::post('/cek', [StatusController::class, 'cek'])->middleware('throttle:10,1')->name('cek');
Route::get('/status/{kode}', [StatusController::class, 'tampil'])->middleware('throttle:10,1')->name('status'); // tautan langsung ke status

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Lupa password: admin/teknisi meminta persetujuan owner; owner bisa langsung ganti dengan kunci pemulihan
Route::get('/lupa-sandi', [LupaSandiController::class, 'form'])->middleware('guest')->name('sandi.lupa');
Route::post('/lupa-sandi', [LupaSandiController::class, 'kirim'])->middleware(['guest', 'throttle:5,1'])->name('sandi.kirim');

// Panel (wajib login). Hak akses per role diatur oleh Gate di AppServiceProvider.
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    // owner saja
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('audit', [AuditController::class, 'index'])->middleware('can:lihat-audit')->name('audit.index');

    // Cadangan data: admin & owner boleh membuat; hanya owner boleh memulihkan
    Route::get('cadangan', [CadanganController::class, 'index'])->middleware('can:buat-cadangan')->name('cadangan.index');
    Route::post('cadangan/unduh', [CadanganController::class, 'unduh'])->middleware(['can:buat-cadangan', 'throttle:10,1'])->name('cadangan.unduh');
    Route::post('cadangan/pulihkan', [CadanganController::class, 'pulihkan'])->middleware(['can:pulihkan-data', 'throttle:10,1'])->name('cadangan.pulihkan');

    // admin & owner: tambah data + perbarui status.
    // (didaftarkan SEBELUM servis/{servis} agar "create" tidak dianggap sebagai ID)
    Route::middleware('can:kelola-servis')->group(function () {
        Route::get('servis/create', [ServisController::class, 'create'])->name('servis.create');
        Route::get('servis/cek-plat', [ServisController::class, 'cekPlat'])->middleware('throttle:60,1')->name('servis.cek-plat');
        Route::post('servis', [ServisController::class, 'store'])->name('servis.store');
        Route::get('servis/{servis}/edit', [ServisController::class, 'edit'])->name('servis.edit');
        Route::put('servis/{servis}', [ServisController::class, 'update'])->name('servis.update');
    });

    // owner saja: hapus data
    Route::delete('servis/{servis}', [ServisController::class, 'destroy'])
        ->middleware('can:hapus-servis')->name('servis.destroy');

    // teknisi, admin, owner: hanya melihat
    Route::middleware('can:lihat-servis')->group(function () {
        Route::get('servis', [ServisController::class, 'index'])->name('servis.index');
        Route::get('servis/{servis}', [ServisController::class, 'show'])->name('servis.show');

        // Daftar motor langganan (plat sama masuk lebih dari 3 kali) + riwayat kerusakan & perbaikannya
        Route::get('langganan', [LanggananController::class, 'index'])->name('langganan.index');
        Route::get('langganan/{servis}', [LanggananController::class, 'show'])->name('langganan.show');
    });
});

// Kelola pengguna: owner saja
Route::middleware(['auth', 'can:kelola-pengguna'])->prefix('admin')->name('admin.')->group(function () {
    // persetujuan reset password (owner saja)
    Route::get('reset-sandi', [ResetSandiController::class, 'index'])->name('reset.index');
    Route::post('reset-sandi/{permintaan}/setujui', [ResetSandiController::class, 'setujui'])->name('reset.setujui');
    Route::post('reset-sandi/{permintaan}/tolak', [ResetSandiController::class, 'tolak'])->name('reset.tolak');

    Route::resource('pengguna', PenggunaController::class)
        ->parameters(['pengguna' => 'pengguna'])
        ->except('show');
});
