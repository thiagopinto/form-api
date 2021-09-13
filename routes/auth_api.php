<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])
    ->name('api-csrf-cookie');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'storeApi'])
    ->middleware('guest')
    ->name('login');

Route::delete('/logout', [AuthenticatedSessionController::class, 'destroyApi'])
    ->middleware('auth:sanctum')
    ->name('logout');

Route::get('/me', [AuthenticatedSessionController::class, 'me'])
    ->middleware('auth:sanctum')
    ->name('me');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

/*

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
->middleware('guest')
->name('password.reset');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
->middleware('guest')
->name('password.request');

Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
->middleware('auth')
->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
->middleware(['auth', 'signed', 'throttle:6,1'])
->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
->middleware(['auth', 'throttle:6,1'])
->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
->middleware('auth')
->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
->middleware('auth');

 */
