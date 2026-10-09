<?php

use App\Http\Controllers\Admin\JasaServisController;
use App\Http\Controllers\Admin\KasirAccountController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\MekanikController;
use App\Http\Controllers\Admin\SparepartController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman Utama -> Redirect Sesuai Status Login & Role
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('kasir.dashboard');
    }
    return redirect()->route('login');
});

// Guest Routes (Tamu Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes (Harus Login & Akun Aktif)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Ubah Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Area Admin (Khusus Role Admin)
    Route::middleware('role:admin')->prefix('admin')->as('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // Master Data Kategori
        Route::resource('kategori', KategoriController::class)->except(['create', 'edit']);

        // Master Data Sparepart & Produk
        Route::resource('spareparts', SparepartController::class);

        // Master Data Jasa Servis
        Route::resource('jasa', JasaServisController::class)->except(['create', 'edit']);

        // Master Data Mekanik
        Route::resource('mekanik', MekanikController::class)->except(['create', 'edit']);

        // Kelola Akun Kasir
        Route::resource('kasir', KasirAccountController::class)->only(['index', 'store', 'update']);
        Route::patch('kasir/{kasir}/toggle-status', [KasirAccountController::class, 'toggleStatus'])->name('kasir.toggleStatus');
    });

    // Area Kasir (Khusus Role Kasir)
    Route::middleware('role:kasir')->prefix('kasir')->as('kasir.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kasir'])->name('dashboard');
    });
});
