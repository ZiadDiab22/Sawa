<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\Api\AboutUsController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\CityController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Driver\DriverController;
use App\Http\Controllers\Api\Passenger\UserController;
use App\Http\Controllers\Api\Ride\RideRequestController;
use App\Http\Controllers\Api\Admin\VehicleTypeController;
use App\Http\Controllers\Api\Passenger\ProfileController;
use App\Http\Controllers\Api\Driver\DriverRatingController;
use App\Http\Controllers\Api\Admin\DriverApprovalController;
use App\Http\Controllers\Api\Driver\DriverDocumentController;
use App\Http\Controllers\Api\Driver\DriverProfileController;
use App\Http\Controllers\Api\Driver\DriverWalletController;
use App\Http\Controllers\Api\Ride\RideController;

Route::prefix('user')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/verifyOtp', [AuthController::class, 'verifyOtp']);

    Route::middleware(['auth:sanctum', 'check_user'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('rating', [DriverRatingController::class, 'store']);
        Route::put('rating/{id}', [DriverRatingController::class, 'update']);
        Route::delete('rating/{id}', [DriverRatingController::class, 'destroy']);
        Route::post('/ride-request', [RideRequestController::class, 'store']);
        Route::post('/ride-request/{id}/cancel', [RideRequestController::class, 'cancel']);
    });
});

Route::prefix('driver')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
});

//passenger
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});

//Driver

Route::middleware(['auth:sanctum'])->prefix('driver')->group(function () {
    Route::get('/show', [DriverProfileController::class, 'show']);
    Route::post('/update', [DriverProfileController::class, 'update']);
    Route::post('/store', [DriverProfileController::class, 'store']);
    Route::put('/active', [DriverController::class, 'toggleStatus'])->middleware(['check_driver', 'driver.commission.check']);
    Route::post('/ride-request/skip', [RideRequestController::class, 'skip'])->middleware(['check_driver']);
    Route::post('/ride-request/accept', [RideRequestController::class, 'accept'])->middleware(['check_driver']);
    Route::post('/ride/start', [RideController::class, 'start'])->middleware(['check_driver']);
    Route::post('/ride/complete', [RideController::class, 'complete'])->middleware(['check_driver']);
});

Route::middleware(['auth:sanctum'])->prefix('account')->group(function () {
    Route::delete('/delete', [AuthController::class, 'delete']);
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);
    //profile
    Route::middleware(['auth:sanctum', 'check_admin'])->group(function () {
        Route::post('/profile/update', [AdminController::class, 'updateProfile']);
        //city
        Route::post('/city/store', [CityController::class, 'store']);
        Route::put('/city/update/{id}', [CityController::class, 'update']);
        Route::delete('/city/destroy/{id}', [CityController::class, 'destroy']);
        Route::get('/city/index', [CityController::class, 'index']);
        //vehicle-types
        Route::post('/vehicle-types/store', [VehicleTypeController::class, 'store']);
        Route::post('/vehicle-types/update/{id}', [VehicleTypeController::class, 'update']);
        Route::delete('/vehicle-types/destroy/{id}', [VehicleTypeController::class, 'destroy']);
        Route::get('/vehicle-types/index', [VehicleTypeController::class, 'index']);
        //drivers
        Route::put('/driver/accept/{id}', [DriverController::class, 'accept']);
        Route::post('/driver/{id}/wallet/add', [DriverWalletController::class, 'add']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/drivers/pending', [DriverApprovalController::class, 'pendingDrivers']);
        Route::get('/drivers/{id}', [DriverApprovalController::class, 'show']);
        Route::post('/drivers/{id}/approve', [DriverApprovalController::class, 'approve']);
        Route::post('/drivers/{id}/reject', [DriverApprovalController::class, 'reject']);
        Route::get('/drivers/approved', [DriverApprovalController::class, 'approvedDrivers']);
    });
});


//rider and driver
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('show', [AboutUsController::class, 'show']);
});

//Admin
Route::middleware(['auth:sanctum'])
    ->prefix('admin')
    ->group(function () {
        Route::post('updateAboutUs', [AboutUsController::class, 'update']);
        Route::post('storeAboutUs', [AboutUsController::class, 'store']);
    });

//  documents

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/driver/documents', [DriverDocumentController::class, 'store']);
    Route::post('/driver/documents/{id}', [DriverDocumentController::class, 'update']);
    Route::get('/driver/documents', [DriverDocumentController::class, 'show']);
    //Admin
    Route::put('/admin/driver-documents/{id}/approve', [DriverDocumentController::class, 'approve'])->middleware(['check_admin']);
    Route::put('/admin/driver-documents/{id}/reject', [DriverDocumentController::class, 'reject'])->middleware(['check_admin']);
    Route::get('/admin/driver-documents/pending', [DriverDocumentController::class, 'pendingDocuments'])->middleware(['check_admin']);
});
