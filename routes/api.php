<?php

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

Route::prefix('v1')
    ->middleware(['api.token', 'throttle:api'])
    ->group(function () {
        Route::get('/translations/search', [TranslationController::class, 'search']);
        Route::get('/translations/export', [TranslationController::class, 'export']);
        Route::get('/translations/{id}', [TranslationController::class, 'show']);
        Route::post('/translations', [TranslationController::class, 'store']);
        Route::put('/translations/{id}', [TranslationController::class, 'update']);
        Route::delete('/translations/{id}', [TranslationController::class, 'destroy']);
    });
