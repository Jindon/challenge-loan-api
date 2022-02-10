<?php

use App\Http\Controllers\FullPaymentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/loans', [LoanController::class, 'index']);
    Route::post('/loans', [LoanController::class, 'store']);
    Route::get('/loans/{loan}', [LoanController::class, 'show']);

    Route::get('/loans/{loan}/payments', [PaymentController::class, 'index']);
    Route::put('/loans/{loan}/payments/{payment}', [PaymentController::class, 'update']);
    Route::post('/loans/{loan}/payments/pay-full', FullPaymentController::class);
});
