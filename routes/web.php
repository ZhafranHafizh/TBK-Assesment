<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
use App\Http\Controllers\CoaCategoryController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\ReportController;

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SettingController;

Route::middleware('auth')->group(function () {
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::post('/archive/restore/{type}/{id}', [ArchiveController::class, 'restore'])->name('archive.restore');

    Route::resource('coa-categories', CoaCategoryController::class);
    Route::resource('coas', CoaController::class);
    Route::resource('transactions', FinancialTransactionController::class);

    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/app-name', [SettingController::class, 'updateAppName'])->name('settings.update-app-name');
    Route::post('/settings/currency', [SettingController::class, 'addCurrency'])->name('settings.add-currency');
    Route::delete('/settings/currency/{id}', [SettingController::class, 'removeCurrency'])->name('settings.remove-currency');
    Route::post('/settings/fetch-rates', [SettingController::class, 'fetchRates'])->name('settings.fetch-rates');
    Route::post('/settings/fetch-historical-rate', [SettingController::class, 'fetchHistoricalRate'])->name('settings.fetch-historical-rate');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
