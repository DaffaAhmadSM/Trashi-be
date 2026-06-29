<?php

use App\Http\Controllers\address\AddressController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', fn (Request $request) => $request->user());
        Route::post('update', [AuthController::class, 'update']);
        Route::delete('delete', [AuthController::class, 'deleteAccount']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('addresses', AddressController::class);
});
