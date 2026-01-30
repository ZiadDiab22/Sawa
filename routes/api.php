<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use \App\Http\Controllers\Api\Ride\RideInfoController;
use App\Http\Controllers\Api\AboutUsController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\CityController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\DriverProfitController;
use App\Http\Controllers\Api\Admin\CompanyProfitController;
use App\Http\Controllers\Api\Driver\DriverController;
use App\Http\Controllers\Api\Passenger\UserController;
use App\Http\Controllers\Api\Ride\RideRequestController;
use App\Http\Controllers\Api\Admin\VehicleTypeController;
use App\Http\Controllers\Api\Passenger\ProfileController;
use App\Http\Controllers\Api\Driver\DriverRatingController;
use App\Http\Controllers\Api\Passenger\PassengerRatingController;
use App\Http\Controllers\Api\Admin\DriverApprovalController;
use App\Http\Controllers\Api\Driver\DriverDashboardController;
use App\Http\Controllers\Api\Driver\DriverDocumentController;
use App\Http\Controllers\Api\Driver\DriverHistoryController;
use App\Http\Controllers\Api\Driver\DriverProfileController;
use App\Http\Controllers\Api\Driver\DriverWalletController;
use App\Http\Controllers\Api\Ride\RideController;
use \App\Http\Controllers\Api\Driver\DriverLocationController;


Route::prefix('user')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/verifyOtp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);


    Route::middleware(['auth:sanctum', 'check_user'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('rating', [DriverRatingController::class, 'store']);
        Route::put('rating/{id}', [DriverRatingController::class, 'update']);
        Route::delete('rating/{id}', [DriverRatingController::class, 'destroy']);
        Route::post('/ride-request', [RideRequestController::class, 'store']);
        Route::post('/ride-request/{id}/cancel', [RideRequestController::class, 'cancel']);
        Route::post('/ride/{id}/cancel', [RideController::class, 'userCancel']);
        Route::get('/history', [RideRequestController::class, 'history']);
        Route::get('/historyById', [RideRequestController::class, 'historyById']);
        Route::get('/completed', [RideRequestController::class, 'completed']);

    });
});

Route::prefix('driver')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/resend-otp', [DriverController::class, 'resendOtp']);
});

//passenger
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('/show', [ProfileController::class, 'show']);
    Route::post('/update', [ProfileController::class, 'update']);
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
    Route::post('/ride/{id}/cancel', [RideController::class, 'driverCancel']);
    Route::get('/history', [DriverHistoryController::class, 'index'])->middleware(['check_driver']);
    Route::get('/dashboard', [DriverDashboardController::class, 'index'])->middleware(['check_driver']);
    Route::get('/stats', [DriverDashboardController::class, 'show'])->middleware(['check_driver']);
    Route::post('rating', [PassengerRatingController::class, 'store'])->middleware(['check_driver']);
    Route::put('rating/{id}', [PassengerRatingController::class, 'update'])->middleware(['check_driver']);
    Route::delete('rating/{id}', [PassengerRatingController::class, 'destroy'])->middleware(['check_driver']);
    Route::post('/location', [DriverLocationController::class, 'store'])->middleware(['check_driver']);
    Route::get('/location', [DriverLocationController::class, 'show'])->middleware(['check_driver']);

    Route::delete( '/deleteFile/{field}',[DriverProfileController::class, 'deleteFile']);
    Route::post('/updateFile/{field}',[DriverProfileController::class, 'updateFile'] );
    Route::delete('/deleteVehicleImage',[DriverProfileController::class, 'deleteVehicleImage']);
    Route::get('/profile/basicInfo',[DriverProfileController::class, 'basicInfo']);
    Route::post('/profile/updateBasicInfo',[DriverProfileController::class, 'updateBasicInfo'] );
    Route::get('/profile/showVehicle',[DriverProfileController::class, 'showVehicle']);
    Route::get('/profile/status',[DriverProfileController::class, 'status']);

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
        Route::get('/rides', [RideInfoController::class, 'index']);
        Route::get('/rides/{ride_id}', [RideInfoController::class, 'show']);
        Route::get('/driver/stats/{id}', [DriverProfitController::class, 'show'])->middleware(['check_admin']);
        Route::get('/company-stats', [CompanyProfitController::class, 'show'])->middleware(['check_admin']);
        Route::get('/driver-locations', [DriverLocationController::class, 'index'])->middleware(['check_admin']);
    });
});


//rider and driver
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('show', [AboutUsController::class, 'show']);
});

//Admin
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        Route::post('updateAboutUs', [AboutUsController::class, 'update']);
        Route::post('storeAboutUs', [AboutUsController::class, 'store']);
        //test
        Route::get('/drivers/approved/count',[AdminController::class, 'approvedDriversCount']);
        Route::get('/drivers/pending/count',[AdminController::class, 'pendingDriversCount']);
        Route::get('/passengers/count', [AdminController::class,'passengersCount']);
        Route::get('/rides/count',[AdminController::class, 'totalRidesCount']);
        Route::get('/rides/completed/count',[AdminController::class, 'completedRidesCount']);
        Route::get('/rides/last-five',[AdminController::class, 'lastFiveCompletedRides']);
        //vehicle_types
        Route::get('/vehicle-types',[AdminController::class, 'allVehicleTypes']);
        Route::patch('/vehicle-types/{id}/toggle-status', [AdminController::class, 'toggleVehicleTypeStatus']);
        Route::delete('/vehicle-types/bulk-delete',[AdminController::class, 'deleteVehicleTypes']);
        Route::get('/vehicle-types/search',[AdminController::class, 'searchVehicleTypes']);
        //vehicle_Makes
        Route::post('/vehicle-makes',[AdminController::class, 'storeVehicleMake']);
        Route::get('/vehicle-makes/search',[AdminController::class, 'searchVehicleMakes']);
        Route::get('/vehicle-makes/getAll', [AdminController::class, 'getAllVehicleMakes']);
        Route::patch('/vehicle-makes/{id}/toggle-status',[AdminController::class, 'toggleVehicleMakeStatus']);
        Route::delete('/vehicle-makes/bulk-delete',[AdminController::class, 'deleteVehicleMakes']);
        Route::get('/vehicle-makes/filter-by-type',[AdminController::class, 'filterVehicleMakesByType']);

        //CancellationReason
        Route::get('cancellation-reasons/index',[AdminController::class, 'index']);
        Route::get('cancellation-reasons/SearchCancellationReason',[AdminController::class, 'SearchCancellationReason']);
        Route::post('cancellation-reasons/store',[AdminController::class, 'store']);
        Route::put('cancellation-reasons/{id}',[AdminController::class, 'update']);
        Route::patch('cancellation-reasons/{id}/toggle-status',[AdminController::class, 'toggleStatus']);
        Route::delete('cancellation-reasons/bulk-delete',[AdminController::class, 'bulkDestroy']);
        //Driver Management
        //DriversList
        Route::get('/listDrivers', [AdminController::class, 'listDrivers']);
        Route::post('/approve/{id}', [AdminController::class, 'approveDriver']);
        Route::post('/suspend/{id}', [AdminController::class, 'suspendDriver']);
        Route::get('/active', [AdminController::class, 'listActiveDrivers']);
        Route::get('/inactive', [AdminController::class, 'listInactiveDrivers']);
        Route::get('/pending', [AdminController::class, 'listPendingDrivers']);
        Route::patch('/toggle-receiving-requests/{driverId}',[AdminController::class,'toggleReceivingRequests']);
        Route::get('/searchDrivers', [AdminController::class, 'searchDrivers']);
        Route::get('/documentsByDriver/{driverId}',[AdminController::class, 'documentsByDriver']);
        Route::post('/driver-documents/{id}/approve',[AdminController::class, 'approveDocument']);
        Route::post('/driver-documents/{id}/reject',[AdminController::class, 'rejectDocument']);


});

//  documents

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/driver/documents', [DriverDocumentController::class, 'store']);
    // Route::post('/driver/documents/{id}', [DriverDocumentController::class, 'update']);
    // Route::get('/driver/documents', [DriverDocumentController::class, 'show']);
    Route::post('/driver/documents/store',[DriverDocumentController::class,'store']);
    Route::post('/driver/documents/update/{id}',[DriverDocumentController::class,'update']);
    Route::get('/driver/documents/show',[DriverDocumentController::class,'show']);
    Route::delete('/driver/documents/deleteFile/{id}',[DriverDocumentController::class, 'deleteFile']);
    Route::post('/driver/documents/updateFile/{id}',[DriverDocumentController::class, 'updateFile']);
    Route::get('/driver/documents/showGrouped',[DriverDocumentController::class,'showGrouped']);
});

    //Admin
    Route::put('/admin/driver-documents/{id}/approve', [DriverDocumentController::class, 'approve'])->middleware(['check_admin']);
    Route::put('/admin/driver-documents/{id}/reject', [DriverDocumentController::class, 'reject'])->middleware(['check_admin']);
    Route::get('/admin/driver-documents/pending', [DriverDocumentController::class, 'pendingDocuments'])->middleware(['check_admin']);
