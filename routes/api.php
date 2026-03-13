<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TransactionController;

use App\Http\Controllers\UserController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

// Public Purchase route
Route::post('transactions', [TransactionController::class, 'store']);

Route::middleware(['auth:api', 'role:admin,manager'])->group(function () {
    Route::post('gateways/{gateway}/activate', [GatewayController::class, 'activate']);
    Route::post('gateways/{gateway}/deactivate', [GatewayController::class, 'deactivate']);
    Route::put('gateways/{gateway}/priority', [GatewayController::class, 'changePriority']);
    
    Route::apiResource('products', ProductController::class);
});

Route::middleware(['auth:api', 'role:admin,manager,finance'])->group(function () {
    Route::get('clients', [ClientController::class, 'index']);
    Route::get('clients/{client}', [ClientController::class, 'show']);

    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::post('transactions/{transaction}/refund', [TransactionController::class, 'refund']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('users', UserController::class);
});
