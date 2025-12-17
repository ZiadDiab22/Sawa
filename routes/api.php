<?php

use App\Http\Controllers\Api\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Passenger\UserController;
use App\Http\Controllers\Api\Passenger\ProfileController;
use App\Http\Controllers\Api\Admin\DriverApprovalController;
use App\Http\Controllers\Api\Driver\DriverController;
use App\Http\Controllers\Api\Driver\DriverDocumentController;
use App\Http\Controllers\Api\Driver\DriverProfileController;
use App\Http\Controllers\Api\Driver\DriverRatingController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('user')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/verifyOtp', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('rating', [DriverRatingController::class, 'store']);
        Route::put('rating/{id}', [DriverRatingController::class, 'update']);
        Route::delete('rating/{id}', [DriverRatingController::class, 'destroy']);
    });
});

Route::prefix('driver')->group(function () {
    Route::post('/register', [DriverController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
});

//passenger
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});

//Driver
Route::middleware(['auth:sanctum'])->prefix('driver')->group(function () {
    Route::get('/profile', [DriverProfileController::class, 'show']);
    Route::put('/profile', [DriverProfileController::class, 'update']);
});


Route::middleware(['auth:sanctum'])->prefix('driver')->group(function () {
    Route::get('/documents', [DriverDocumentController::class, 'index']);
    Route::post('/documents', [DriverDocumentController::class, 'store']);
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);

    Route::middleware(['auth:sanctum', 'check_admin'])->group(function () {
        Route::post('/profile/update', [AdminController::class, 'updateProfile']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/drivers/pending', [DriverApprovalController::class, 'pendingDrivers']);
        Route::get('/drivers/{id}', [DriverApprovalController::class, 'show']);
        Route::post('/drivers/{id}/approve', [DriverApprovalController::class, 'approve']);
        Route::post('/drivers/{id}/reject', [DriverApprovalController::class, 'reject']);
        Route::get('/drivers/approved', [DriverApprovalController::class, 'approvedDrivers']);
    });
});
