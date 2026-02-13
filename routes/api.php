<?php
declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
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
});
