<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RequestAsSellerController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController as ControllersProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\CategoryController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\SellerController as SellerSellerController;
use App\Http\Controllers\SellerProfileController;
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
    Route::get('admin-dashboard', [DashboardController::class, 'dashboard']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/ban', [UserController::class, 'banUser']);
    Route::post('users/{id}/unban', [UserController::class, 'unbanUser']);
    Route::apiResource('sellers', SellerController::class);
    Route::apiResource('request-as-sellers', RequestAsSellerController::class);
    Route::post('seller-approval/{id}', [RequestAsSellerController::class, 'approval']);
    Route::post('seller-rejected/{id}', [RequestAsSellerController::class, 'rejected']);
    Route::apiResource('categories',  CategoryController::class);
});

// Seller routes
Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::prefix('seller')->group(function () {
        Route::get('dashboard', [SellerController::class, 'dashboard']);
        Route::apiResource('categories',  CategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::get('product/search', [ProductController::class, 'search']);
        
    });
});

Route::prefix('seller-profile')->group(function () {
    Route::get('profile', [SellerProfileController::class, 'sellerProfile']);
    Route::get('product-list', [SellerProfileController::class, 'sellerProfileProductList']);
});

Route::post('request-as-seller', [RequestAsSellerController::class, 'requestAsSeller']);
Route::get('seller-profile/{id}', [SellerProfileController::class, 'sellerProfile']);
Route::get('seller-profile-product-list/{id}', [SellerProfileController::class, 'sellerProfileProductList']);

Route::prefix('home')->group(function () {
    Route::get('category', [HomeController::class, 'category']);
    Route::get('top-sale-today', [HomeController::class, 'topSaleToday']);
    Route::get('best-selling-product', [HomeController::class, 'bestSellingProduct']);
    Route::get('explore-our-product', [HomeController::class, 'exploreOurProduct']);
});


Route::prefix('product')->group(function () {
    Route::apiResource('products', \App\Http\Controllers\ProductController::class);
});

Route::prefix('profile')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('buyer-profile', [ProfileController::class, 'profile']);
        Route::patch('profile-update', [ProfileController::class, 'update']);
        Route::apiResource('addresses', AddressController::class);
    });
});
