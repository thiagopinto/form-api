<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HealthUnitController;
use App\Http\Controllers\Api\V1\CidController;
use App\Http\Controllers\Api\V1\CidGroupController;
use App\Http\Controllers\Api\V1\CidChapterController;
use App\Http\Controllers\Api\V1\QueryFilterController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(
    function () {
        require __DIR__ . '/auth.php';
        require __DIR__ . '/dataset_sih.php';
        require __DIR__ . '/dataset.php';
        require __DIR__ . '/location.php';

        Route::get('health_unit', [HealthUnitController::class, 'index'])->name('health_unit.index');
        Route::get('health_unit/{id}', [HealthUnitController::class, 'show'])->name('health_unit.show');
        Route::patch('health_unit/{id?}', [HealthUnitController::class, 'partialUpdate'])->name('health_unit.partial_update');

        Route::get('cid', [CidController::class, 'index'])->name('cid.index');
        Route::get('cid/chapter', [CidChapterController::class, 'index'])->name('cid_chapter.index');
        Route::get('cid/group', [CidGroupController::class, 'index'])->name('cid_group.index');

        Route::get('query_filter', [QueryFilterController::class, 'index'])->name('query_filter.index');
        Route::get('query_filter/{id}', [QueryFilterController::class, 'show'])->name('query_filter.show');
        Route::middleware('auth:sanctum')->group(
            function () {
                Route::post('query_filter', [QueryFilterController::class, 'store'])->name('query_filter.store');
                Route::delete('query_filter/{id}', [QueryFilterController::class, 'destroy'])->name('query_filter.delete');
            }
        );
    }
);


