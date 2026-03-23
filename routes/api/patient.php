<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientRecordController;
use App\Http\Controllers\QRTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('patient')->group(function () {

    Route::post('/claim', [AuthController::class, 'claimCredential'])->name('admin.user.claim');

    // Login using credential token
    Route::post('/login', [AuthController::class, 'login'])->name('patient.login');

    // Get authenticated user info
    Route::post('/me', [AuthController::class, 'getMe'])->name('patient.me');

    //scan qr code to hospital qr code to get the claim token and then use the claim token to claim the record
    Route::post('/hospital-scan', [QRTokenController::class, 'approve'])->name('patient.hospital.approve');

    Route::prefix('record')->group(function () {
        Route::get('/', [PatientRecordController::class, 'index']);
        Route::get('/{id}', [PatientRecordController::class, 'show']);
    });
});