<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TranslationController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')->group(function () {
    // Public endpoint to obtain a token
    Route::post('/auth/token', [AuthController::class, 'issueToken']);

    Route::middleware(['api.token', 'throttle:api'])
        ->prefix('translations')
        ->group(function () {
            Route::post('', [TranslationController::class, 'store']);
            Route::get('/export/locale/{locale}', [TranslationController::class, 'export']);
            Route::get('/search', [TranslationController::class, 'search']);
            Route::put('/{translation}', [TranslationController::class, 'update']);
            Route::delete('/{translation}', [TranslationController::class, 'destroy']);
            Route::get('/{translation}', [TranslationController::class, 'show']);
        });
});
