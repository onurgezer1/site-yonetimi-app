<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes are used for user authentication. They handle login,
| registration, email verification, and password reset functionality.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

    Route::get('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
                ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [App\Http\Controllers\Auth\EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);

    Route::put('password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});