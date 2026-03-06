<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('patient')->group(function () {

    Route::post('/claim', [AuthController::class, 'claimCredential'])->name('admin.user.claim');

    // Login using credential token
    Route::post('/login', [AuthController::class, 'login'])->name('patient.login');

    // Get authenticated user info
    Route::post('/me', [AuthController::class, 'getMe'])->name('patient.me');

    Route::prefix('record')->group(function () {
        Route::get('/', [PatientRecordController::class, 'index']);
        Route::get('/{id}', [PatientRecordController::class, 'show']);
    });
});