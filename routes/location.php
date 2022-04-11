<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Location\NeighborhoodController;

Route::get('/location/neighborhood', [NeighborhoodController::class, 'index'])->name('neighborhood-dvs.index');
Route::get('/location/neighborhood/map', [NeighborhoodController::class, 'map'])->name('neighborhood-dvs.map');
