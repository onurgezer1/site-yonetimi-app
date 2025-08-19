<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AidatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Site Management Routes
    Route::resource('sites', SiteController::class);
    Route::get('/sites/{site}/apartments', [SiteController::class, 'apartments'])->name('sites.apartments');
    Route::post('/sites/{site}/apartments', [SiteController::class, 'storeApartment'])->name('sites.apartments.store');

    // Aidat Management Routes
    Route::resource('aidats', AidatController::class);
    Route::post('/aidats/generate-monthly', [AidatController::class, 'generateMonthly'])->name('aidats.generate');
    Route::get('/aidats/{aidat}/calculate-late-fee', [AidatController::class, 'calculateLateFee'])->name('aidats.calculate-late-fee');

    // Payment Routes
    Route::resource('payments', PaymentController::class);
    Route::post('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
});

require __DIR__.'/auth.php';