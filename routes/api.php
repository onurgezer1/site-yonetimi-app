<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\AidatController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Site Management API
    Route::apiResource('sites', SiteController::class);
    Route::get('/sites/{site}/statistics', [SiteController::class, 'statistics']);
    Route::get('/sites/{site}/apartments', [SiteController::class, 'apartments']);

    // Aidat Management API
    Route::apiResource('aidats', AidatController::class);
    Route::post('/aidats/generate-monthly', [AidatController::class, 'generateMonthly']);
    Route::get('/aidats/statistics', [AidatController::class, 'statistics']);

    // Payment API
    Route::apiResource('payments', PaymentController::class);
    Route::post('/payments/{payment}/process', [PaymentController::class, 'process']);
    Route::get('/payments/statistics', [PaymentController::class, 'statistics']);
});