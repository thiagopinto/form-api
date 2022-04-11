<?php

use App\Http\Controllers\Api\V1\DatasetController;
use Illuminate\Support\Facades\Route;

Route::get(
    'dataset/{id}',
    [DatasetController::class, 'show']
)->name('show');

Route::get(
    'dataset/year/{source}/{system}/{initial}/{year}',
    [DatasetController::class, 'showYear']
)->name('show-year');

Route::get(
    'dataset/{source}/{system}/{initial}',
    [DatasetController::class, 'index']
)->name('index');

Route::get(
    'dataset/serie/{source}/{system}/{initial}/{id}',
    [DatasetController::class, 'getSerie']
)->name('dataset.serie');

Route::get(
    'dataset/serie/range/{source}/{system}/{initial}/{id}',
    [DatasetController::class, 'getRange']
)->name('dataset.serie-range');



Route::middleware('auth:sanctum')->group(
    function () {
        Route::post(
            'dataset/{source}/{system}/{initial}',
            [DatasetController::class, 'store']
        )->name('dataset.store');

        Route::patch(
            'dataset/{source}/{system}/{initial}/{id}',
            [DatasetController::class, 'update']
        )->name('dataset.partial_update');

        Route::put(
            'dataset/{source}/{system}/{initial}/{id}',
            [DatasetController::class, 'update']
        )->name('dataset.update');

        Route::delete(
            'dataset/{source}/{system}/{initial}/{id}',
            [DatasetController::class, 'destroy']
        )->name('dataset.destroy');
    }
);
