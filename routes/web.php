<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SPOController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MasterAlatController;
use App\Http\Controllers\TelegramController;
// TAMBAHAN BARU:
use App\Http\Controllers\MasterPegawaiController; 

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['verify' => true]);

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    if (request('reason') === 'new_tab') {
        return redirect()->route('login')->with('info', 'Akses tab baru terdeteksi. Sistem keamanan mewajibkan login ulang untuk setiap tab baru.');
    }
    return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
})->name('logout.get');

Route::middleware(['auth'])->group(function () {

    // === DASHBOARD & REALTIME SYNC ===
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/kru/dashboard', [DashboardController::class, 'index'])->name('kru.dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    Route::get('/sync/check', [DashboardController::class, 'syncCheck'])->name('sync.check');

    // === MODUL SPO ===
    Route::patch('/spo/{id}/approve', [SPOController::class, 'approve'])->name('spo.approve');
    Route::patch('/spo/{id}/reject', [SPOController::class, 'reject'])->name('spo.reject');
    Route::get('/spo/{id}/download', [SPOController::class, 'downloadPDF'])->name('spo.download');
    Route::get('/spo/{id}/verify', [SPOController::class, 'verify'])->name('spo.verify');
    Route::post('/spo/{id}/request-revision', [\App\Http\Controllers\SPOController::class, 'requestRevision'])->name('spo.request-revision');
    Route::patch('/spo/revisi/{id}/approve', [\App\Http\Controllers\SPOController::class, 'approveRevision'])->name('spo.approve-revision');
    Route::patch('/spo/revisi/{id}/reject', [\App\Http\Controllers\SPOController::class, 'rejectRevision'])->name('spo.reject-revision');
    Route::resource('spo', SPOController::class);

    // === MODUL MONITORING ===
    Route::get('/monitoring/export/excel', [MonitoringController::class, 'export'])->name('monitoring.export');
    Route::get('/monitoring/export/pdf', [MonitoringController::class, 'exportPdf'])->name('monitoring.export.pdf');
    Route::get('/monitoring/{id}/download-pdf', [MonitoringController::class, 'downloadSinglePdf'])->name('monitoring.download.pdf');
    Route::resource('monitoring', MonitoringController::class);
    Route::post('/monitoring/{id}/request-revision', [MonitoringController::class, 'requestRevision'])->name('monitoring.request-revision');
    Route::patch('/monitoring/{id}/approve-revision', [MonitoringController::class, 'approveRevision'])->name('monitoring.approve-revision');
    Route::patch('/monitoring/{id}/reject-revision', [MonitoringController::class, 'rejectRevision'])->name('monitoring.reject-revision');


    // === MODUL PROFIL ===
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // TAMBAHAN BARU: Rute untuk tombol generate token Telegram & pemutusan sambungan
    Route::post('/profile/telegram-token', [ProfileController::class, 'generateToken'])->name('profile.generate_token');
    Route::delete('/profile/telegram-disconnect', [ProfileController::class, 'disconnectTelegram'])->name('profile.disconnect_telegram');

    // === KHUSUS ADMIN ===
    Route::delete('/users/{id}/disconnect-telegram', [UserController::class, 'disconnectTelegram'])->name('users.disconnect_telegram');
    Route::resource('users', UserController::class);

    // Master Alat
    Route::resource('master/alat', MasterAlatController::class)->names('master.alat');

    // Master Pegawai (INI YANG MEMPERBAIKI ERROR 404)
    Route::resource('master/pegawai', MasterPegawaiController::class)->names('master.pegawai');

});

// Rute Webhook Telegram (Ditaruh di luar middleware auth agar Telegram bisa masuk)
Route::post('/telegram/webhook', [TelegramController::class, 'handle']);