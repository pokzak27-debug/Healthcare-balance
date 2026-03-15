<?php

use App\Http\Controllers\Api\AppointmentApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\PoleApiController;
use App\Http\Controllers\Api\PatientApiController;
use App\Http\Controllers\Api\QuoteApiController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::post('/token', [TokenController::class, 'store']);
Route::delete('/token', [TokenController::class, 'destroy'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('v1')->name('api.')->group(function () {
    Route::apiResource('patients', PatientApiController::class);
    Route::apiResource('appointments', AppointmentApiController::class);
    Route::apiResource('poles', PoleApiController::class);
    Route::apiResource('invoices', InvoiceApiController::class)->only(['index', 'show']);
    Route::apiResource('quotes', QuoteApiController::class)->only(['index', 'show']);
});
