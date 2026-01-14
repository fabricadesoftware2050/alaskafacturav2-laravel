<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MunicipioDepartamentoController;
use App\Http\Controllers\EmpresaController;

use App\Models\User;

Route::prefix('v2')->group(function () {

    // AUTH público
    Route::prefix('auth')
        ->group(function () {
            Route::post('login', [AuthController::class,'login'])->middleware('throttle:without-auth');
            Route::post('register', [AuthController::class,'register'])->middleware('throttle:without-auth');
        });

    // AUTHENTICATED
    Route::middleware('auth:api')->group(function () {

        Route::prefix('auth')
            ->group(function () {
                Route::get('logout', [AuthController::class,'logout'])->middleware('throttle:per-route');
                Route::post('refresh', [AuthController::class,'refresh'])->middleware('throttle:per-route');
                Route::post('verify_account', [AuthController::class,'verify_account'])->middleware('throttle:per-route');
            });
            Route::get('auth/me', [AuthController::class,'me'])->middleware('throttle:me');

        // UPLOADS
        Route::prefix('files')->group(function () {
            Route::post('upload', [FileController::class, 'subir'])
                ->middleware('throttle:asset-upload');

            Route::delete('deleteFile', [FileController::class, 'eliminarArchivo'])
                ->middleware('throttle:per-route');
        });

        // GENERAL
        Route::prefix('general')
        ->middleware(['throttle:global'])->group(function () {
            Route::get('municipios', [MunicipioDepartamentoController::class, 'index']);
            Route::apiResource('empresas', EmpresaController::class);
        });
    });

});


