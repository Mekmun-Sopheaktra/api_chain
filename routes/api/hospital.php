<?php

use App\Http\Controllers\HospitalController;
use App\Http\Controllers\QRTokenController;
use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [HospitalController::class, 'login']);

Route::prefix('hospital')->group(function () {

    //records
    Route::prefix('record')->group(function () {
        Route::get('/', [RecordController::class, 'index']);
        Route::post('/', [RecordController::class, 'store']);
        Route::get('/{id}', [RecordController::class, 'show']);
        Route::post('/{id}', [RecordController::class, 'update']);
        Route::delete('/{id}', [RecordController::class, 'destroy']);
    });

    //generate qr code for patient to scan and get claim token
    Route::get('/generate', [QRTokenController::class, 'generate']);

    //refresh qr code for patient to scan and get claim token
    Route::post('/get-patient', [QRTokenController::class, 'getPatient']);


});