<?php

use App\Http\Controllers\address\AddressController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfficeController;
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
    Route::get('addresses', [AddressController::class, 'index']);
    Route::post('addresses', [AddressController::class, 'store']);
    Route::get('addresses/{address}', [AddressController::class, 'show']);
    Route::post('addresses/{address}', [AddressController::class, 'update']);
    Route::delete('addresses/{address}', [AddressController::class, 'destroy']);

    Route::middleware('admin')->group(function (): void {
        Route::post('articles', [ArticleController::class, 'store']);
        Route::post('articles/{article}', [ArticleController::class, 'update']);
        Route::delete('articles/{article}', [ArticleController::class, 'destroy']);
    });
});

Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);

Route::get('offices', [OfficeController::class, 'index']);
Route::get('offices/{office}', [OfficeController::class, 'show']);

Route::middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::post('offices', [OfficeController::class, 'store']);
    Route::post('offices/{office}', [OfficeController::class, 'update']);
    Route::delete('offices/{office}', [OfficeController::class, 'destroy']);
});
