<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordOtpController;
use App\Http\Controllers\Api\DeliveryController;

/*
|--------------------------------------------------------------------------
| Auth (login/logout/me + password reset via OTP)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    Route::post('send-otp', [PasswordOtpController::class, 'sendOtp'])->middleware('throttle:6,1');
    Route::post('reset-password', [PasswordOtpController::class, 'reset'])->middleware('throttle:6,1');
});

/*
|--------------------------------------------------------------------------
| Example protected user endpoint
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', fn (Request $r) => $r->user());

/*
|--------------------------------------------------------------------------
| Deliveries (Courier App) – protected
|--------------------------------------------------------------------------
| Kontrak:
| - POST   /api/deliveries                  -> store (multipart; tanpa lat/lng)
| - GET    /api/deliveries                  -> index (support query: date=today, status, page, limit, dsb.)
| - GET    /api/deliveries/{id}             -> show
| - GET    /api/deliveries/history          -> history (filter: query, status, start, end, page, limit)
| - PATCH  /api/deliveries/{id}/proof       -> uploadProof (multipart: recipient_name, proof_photo)
*/
Route::middleware('auth:sanctum')->group(function () {
    // Riwayat terpisah agar query param lebih eksplisit
    Route::get('deliveries/history', [DeliveryController::class, 'history'])
        ->name('deliveries.history');

    // Upload bukti serah (foto + recipient_name)
    Route::patch('deliveries/{delivery}/proof', [DeliveryController::class, 'uploadProof'])
        ->whereNumber('delivery')
        ->middleware('throttle:30,1')
        ->name('deliveries.proof');

    // Resource utama (hanya yang dibutuhkan untuk MVP)
    Route::apiResource('deliveries', DeliveryController::class)
        ->only(['index', 'store', 'show']);
});
