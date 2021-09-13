<?php

use App\Http\Controllers\Api\V1\DataSetController;
use Illuminate\Support\Facades\Route;

Route::get(
    'dataset/{source}/{datasetName}/',
    [DataSetController::class, 'index']
)->name('index');


Route::middleware('auth:sanctum')->group(
    function () {
        Route::post(
            'dataset/{source}/{datasetName}/',
            [DataSetController::class, 'store']
        )->name('store');
        Route::patch(
            'dataset/{source}/{datasetName}/{id}',
            [DataSetController::class, 'update']
        )->name('partial_update');
        Route::put(
            'dataset/{source}/{datasetName}/{id}',
            [DataSetController::class, 'update']
        )->name('update');
        Route::delete(
            'dataset/{source}/{datasetName}/{id}',
            [DataSetController::class, 'destroy']
        )->name('delete');
    }
);
