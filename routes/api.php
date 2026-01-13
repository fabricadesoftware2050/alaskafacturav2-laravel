<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MunicipioDepartamentoController;

use App\Models\User;

Route::group(['prefix' => 'v2'], function () {
    
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class,'login']); // abierta
        Route::post('register', [AuthController::class,'register']); // abierta
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => 'auth'], function () {

            Route::get('logout', [AuthController::class,'logout']);
            Route::post('refresh', [AuthController::class,'refresh']);
            Route::post('verify_account', [AuthController::class,'verify_account']);
            Route::get('me', [AuthController::class,'me']);
        });
        
        Route::group(['prefix' => 'files'], function () {
             Route::post('/upload', [FileController::class, 'subir']);
             Route::delete('/deleteFile', [FileController::class, 'eliminarArchivo']);
        });
        Route::group(['prefix' => 'general'], function () {
            Route::get('/municipios', [MunicipioDepartamentoController::class, 'index']);
        });

    });

 


});




