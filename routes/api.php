<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\HealthUnitController;
use App\Http\Controllers\Api\V1\BornAliveFormController;
use App\Http\Controllers\Api\V1\DeathCertificateFormController;
use App\Http\Controllers\Api\V1\ReceiptController;
use App\Http\Controllers\Api\V1\CidController;
use App\Http\Controllers\Api\V1\CidChapterController;
use App\Http\Controllers\Api\V1\CidGroupController;
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

Route::prefix('v1')->group(
    function () {
        include __DIR__ . '/auth_api.php';
        include __DIR__ . '/dataset.php';
        include __DIR__ . '/location.php';

        Route::get('health_unit', [HealthUnitController::class, 'index']);
        Route::get('health_unit/{id}', [HealthUnitController::class, 'show']);
        Route::patch('health_unit/{id?}', [HealthUnitController::class, 'partial_update']);
        Route::post('health_unit', [HealthUnitController::class, 'store']);
        Route::put('health_unit/{id}', [HealthUnitController::class, 'update']);
        Route::get('/receipt', [ReceiptController::class, 'index']);

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

        Route::middleware('auth:sanctum')->group(
            function () {
                Route::get('born_alive_form', [BornAliveFormController::class, 'index']);
                Route::post('born_alive_form', [BornAliveFormController::class, 'store']);
                Route::put('born_alive_form/{id?}', [BornAliveFormController::class, 'update']);
                Route::patch('born_alive_form/{id?}', [BornAliveFormController::class, 'partial_update']);
                Route::get('born_alive_form/receipt/{id?}', [BornAliveFormController::class, 'receipt']);
                Route::get('born_alive_form/report', [BornAliveFormController::class, 'indexReport']);
                Route::get('born_alive_form/report/pdf', [BornAliveFormController::class, 'report']);
                Route::get('born_alive_form/report/health_unit/{cnes}', [BornAliveFormController::class, 'exportByHealthUnit']);
                Route::get('born_alive_form/report/health_unit/{cnes}/pdf', [BornAliveFormController::class, 'reportByHealthUnit']);
                Route::get('born_alive_form/count/{status?}', [BornAliveFormController::class, 'count']);
                Route::get('born_alive_form/last-send/{cnes_code?}', [BornAliveFormController::class, 'lastSend']);
                Route::patch('born_alive_form/reversal/{id?}', [BornAliveFormController::class, 'reversal']);


                Route::get('death_certificate_form', [DeathCertificateFormController::class, 'index']);
                Route::post('death_certificate_form', [DeathCertificateFormController::class, 'store']);
                Route::put('death_certificate_form/{id?}', [DeathCertificateFormController::class, 'update']);
                Route::patch('death_certificate_form/{id?}', [DeathCertificateFormController::class, 'partial_update']);
                Route::get('death_certificate_form/receipt/{id?}', [DeathCertificateFormController::class, 'receipt']);
                Route::get('death_certificate_form/report', [DeathCertificateFormController::class, 'indexReport']);
                Route::get('death_certificate_form/report/pdf', [DeathCertificateFormController::class, 'report']);
                Route::get('death_certificate_form/report/health_unit/{cnes}', [DeathCertificateFormController::class, 'exportByHealthUnit']);
                Route::get('death_certificate_form/report/health_unit/{cnes}/pdf', [DeathCertificateFormController::class, 'reportByHealthUnit']);
                Route::get('death_certificate_form/count/{status?}', [DeathCertificateFormController::class, 'count']);
                Route::get('death_certificate_form/last-send/{cnes_code?}', [DeathCertificateFormController::class, 'lastSend']);
                Route::patch('death_certificate_form/reversal/{id?}', [DeathCertificateFormController::class, 'reversal']);

                Route::get('role', [RoleController::class, 'index']);
                Route::resource('user', UserController::class);
                Route::get('users/check-email/{email}', [UserController::class, 'checkEmail']);
            }
        );

    }
);
