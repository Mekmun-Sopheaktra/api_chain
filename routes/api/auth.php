<?php

use App\Http\Controllers\HospitalController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Login using credential token
    Route::post('/login', [HospitalController::class, 'login']);
});