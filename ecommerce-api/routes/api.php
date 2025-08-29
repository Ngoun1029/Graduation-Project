<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Seller\SellerController as SellerSellerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    // Protected JWT routes
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Admin routes
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/ban', [UserController::class, 'banUser']);
    Route::post('users/{id}/unban', [UserController::class, 'unbanUser']);
    Route::apiResource('sellers', SellerController::class);
    Route::put('sellers/{id}/approval', [SellerController::class, 'approval']);
    Route::put('sellers/{id}/rejection', [SellerSellerController::class, 'rejection']);
});

// Seller routes
Route::middleware(['auth:api', 'role:seller'])->group(function () {
    // seller-specific endpoints
});

// Buyer routes
Route::middleware(['auth:api', 'role:buyer'])->group(function () {
    // buyer-specific endpoints
});
