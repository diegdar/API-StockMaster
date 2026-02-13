<?php
declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user.profile');

    Route::apiResource('products', ProductController::class)->names('products');
});
