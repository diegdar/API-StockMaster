<?php
declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login')->middleware('throttle:5,1');

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user.profile');

    Route::apiResource('products', ProductController::class)->names('products');
    Route::apiResource('categories', CategoryController::class)->names('categories');

    // Warehouse custom routes (must be before apiResource)
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [WarehouseController::class, 'listWarehouses'])->name('warehouses.index');
        Route::get('/with-capacity', [WarehouseController::class, 'listWarehousesWithCapacity'])->name('warehouses.with-capacity');
        Route::get('/with-inventory-count', [WarehouseController::class, 'listWarehousesWithInventory'])->name('warehouses.with-inventory-count');
        Route::get('/slug/{warehouse:slug}', [WarehouseController::class, 'showBySlug'])->name('warehouses.show-by-slug');
        Route::get('/{warehouse}/capacity', [WarehouseController::class, 'capacity'])->name('warehouses.capacity');
        Route::post('/transfer', [WarehouseController::class, 'transfer'])->name('transfer');
    });

    Route::apiResource('warehouses', WarehouseController::class)->names('warehouses');

});
