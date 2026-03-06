<?php

use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

Route::prefix('hospital')->group(function () {

    //records
    Route::prefix('record')->group(function () {
        Route::get('/', [RecordController::class, 'index']);
        Route::post('/', [RecordController::class, 'store']);
        Route::get('/{id}', [RecordController::class, 'show']);
        Route::post('/{id}', [RecordController::class, 'update']);
        Route::delete('/{id}', [RecordController::class, 'destroy']);
    });
});