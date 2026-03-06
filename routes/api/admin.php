<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HospitalController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {


    Route::prefix('user')->group(function () {
        // 1️⃣ Register basic user (generate claim token / QR)
        Route::post('/register', [AuthController::class, 'register'])->name('admin.user.register');

        // 3️⃣ Revoke credential
        Route::post('/revoke-credential', [AuthController::class, 'revokeCredential'])->name('admin.user.revokeCredential');

        // 4️⃣ Renew credential using NID
        Route::post('/renew-credential', [AuthController::class, 'renewCredentialNid'])->name('admin.user.renewCredential');

        // Create credential for existing user using NID
        Route::post('/create-credential', [AuthController::class, 'createCredentialNid'])->name('admin.user.createCredential');

    });

    // hospital management routes
    Route::prefix('hospital')->group(function () {
        //index
        Route::get('/', [HospitalController::class, 'index'])->name('admin.hospital.index');
        // Show hospital details
        Route::get('/{id}', [HospitalController::class, 'show'])->name('admin.hospital.show');
        // Create hospital
        Route::post('/create', [HospitalController::class, 'store'])->name('admin.hospital.create');

        // Update hospital
        Route::post('/update/{id}', [HospitalController::class, 'update'])->name('admin.hospital.update');
        // Delete hospital
        Route::post('/delete/{id}', [HospitalController::class, 'destroy'])->name('admin.hospital.delete');
    });

});

