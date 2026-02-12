<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/report/weekly', [DashboardController::class, 'weeklyReport'])->name('report.weekly');
Route::get('/report/monthly', [DashboardController::class, 'monthlyReport'])->name('report.monthly');
Route::get('/import', [DashboardController::class, 'importPage'])->name('import.page');
Route::post('/import/upload', [DashboardController::class, 'uploadFile'])->name('import.upload');
Route::get('/export/{type}', [DashboardController::class, 'exportReport'])->name('report.export');
