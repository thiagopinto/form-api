<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailExternalController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

Route::post('/login', [AuthenticatedController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');

Route::post('/me', [AuthenticatedController::class, 'me'])
    ->middleware('auth:sanctum')
    ->name('me');

Route::post('/refresh', [AuthenticatedController::class, 'refresh'])
    ->middleware('guest')
    ->name('refresh');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.reset');
/*
Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
 */
Route::get('/verify-email/{id}/{hash}', [VerifyEmailExternalController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/{provider}/redirect', [AuthenticatedController::class,'redirectToProvider']);
Route::get('/{provider}/callback', [AuthenticatedController::class,'handleProviderCallback']);
