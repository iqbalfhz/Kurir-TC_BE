<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordOtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// AUTH (login/logout/me)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    Route::post('send-otp', [PasswordOtpController::class, 'sendOtp'])->middleware('throttle:6,1');
    Route::post('reset-password', [PasswordOtpController::class, 'reset'])->middleware('throttle:6,1');
});

// contoh protected standar
Route::middleware('auth:sanctum')->get('/user', fn (Request $r) => $r->user());

// Deliveries CRUD for courier app (protected)
use App\Http\Controllers\Api\DeliveryController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('deliveries', DeliveryController::class);
});
