<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\CanteenUserController;
use App\Http\Controllers\Admin\PanelController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::post('/language', [LanguageController::class, 'update'])->name('language.update');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/attendance', [AttendanceController::class, 'store'])
    ->middleware('throttle:attendance')
    ->name('attendance.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', [PanelController::class, 'index'])->name('index');
    Route::post('/users', [CanteenUserController::class, 'store'])->name('users.store');
    Route::put('/users/{canteenUser}', [CanteenUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{canteenUser}', [CanteenUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/legacy-import', [CanteenUserController::class, 'importLegacy'])->name('legacy-import');
    Route::get('/users/{canteenUser}/barcode', [BarcodeController::class, 'show'])->name('users.barcode');
    Route::get('/history/export', [AdminAttendanceController::class, 'export'])->name('history.export');
    Route::get('/recap/export', [ReportController::class, 'export'])->name('recap.export');
});
