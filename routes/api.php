<?php

use App\Http\Controllers\Api\V1\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Api\V1\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\V1\Admin\SettingsController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\VisitorController as AdminVisitorController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Guard\ActivityLogController;
use App\Http\Controllers\Api\V1\Guard\DashboardController as GuardDashboardController;
use App\Http\Controllers\Api\V1\Guard\QrController;
use App\Http\Controllers\Api\V1\Guard\VisitorController as GuardVisitorController;
use App\Http\Controllers\Api\V1\Resident\BookingController as ResidentBookingController;
use App\Http\Controllers\Api\V1\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Api\V1\Resident\FacilityController as ResidentFacilityController;
use App\Http\Controllers\Api\V1\Resident\NotificationController as ResidentNotificationController;
use App\Http\Controllers\Api\V1\Resident\ProfileController;
use App\Http\Controllers\Api\V1\Resident\VisitorController as ResidentVisitorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ---------------------------------------------------------------
    // Auth (public)
    // ---------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

        Route::get('properties', [AuthController::class, 'properties']);
        Route::get('properties/{property}/blocks', [AuthController::class, 'blocks']);
        Route::get('blocks/{block}/units', [AuthController::class, 'units']);
    });

    // ---------------------------------------------------------------
    // Authenticated
    // ---------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // Auth (authenticated)
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('change-password', [AuthController::class, 'changePassword']);
            Route::post('onboarding', [AuthController::class, 'onboarding']);
        });

        // ---------------------------------------------------------------
        // Resident
        // ---------------------------------------------------------------
        Route::prefix('resident')->middleware(['role:resident', 'resident.approved'])->group(function () {
            Route::get('dashboard', [ResidentDashboardController::class, 'dashboard']);

            Route::get('profile', [ProfileController::class, 'show']);
            Route::put('profile', [ProfileController::class, 'update']);

            Route::get('visitors', [ResidentVisitorController::class, 'index']);
            Route::post('visitors', [ResidentVisitorController::class, 'store']);
            Route::get('visitors/{visitor}', [ResidentVisitorController::class, 'show']);
            Route::post('visitors/{visitor}/cancel', [ResidentVisitorController::class, 'cancel']);
            Route::get('visitors/{visitor}/qr', [ResidentVisitorController::class, 'qr']);

            Route::get('facilities', [ResidentFacilityController::class, 'index']);
            Route::get('facilities/{facility}', [ResidentFacilityController::class, 'show']);
            Route::get('facilities/{facility}/availability', [ResidentFacilityController::class, 'availability']);

            Route::get('bookings', [ResidentBookingController::class, 'index']);
            Route::get('bookings/{booking}', [ResidentBookingController::class, 'show']);
            Route::post('bookings', [ResidentBookingController::class, 'store']);
            Route::post('bookings/{booking}/cancel', [ResidentBookingController::class, 'cancel']);

            Route::get('notifications', [ResidentNotificationController::class, 'index']);
            Route::post('notifications/{notification}/read', [ResidentNotificationController::class, 'markRead']);
            Route::post('notifications/read-all', [ResidentNotificationController::class, 'markAllRead']);
        });

        // ---------------------------------------------------------------
        // Guard
        // ---------------------------------------------------------------
        Route::prefix('guard')->middleware('role:guard')->group(function () {
            Route::get('dashboard', [GuardDashboardController::class, 'dashboard']);

            Route::post('qr/validate', [QrController::class, 'validate'])->middleware('throttle:30,1');

            Route::get('visitors', [GuardVisitorController::class, 'index']);
            Route::get('visitors/{visitor}', [GuardVisitorController::class, 'show']);
            Route::post('visitors/{visitor}/check-in', [GuardVisitorController::class, 'checkIn']);
            Route::post('visitors/{visitor}/check-out', [GuardVisitorController::class, 'checkOut']);

            Route::get('activity-logs', [ActivityLogController::class, 'index']);
        });

        // ---------------------------------------------------------------
        // Admin
        // ---------------------------------------------------------------
        Route::prefix('admin')->middleware('role:super_admin|property_admin')->group(function () {
            Route::get('dashboard', [AdminDashboardController::class, 'dashboard']);

            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{user}', [UserController::class, 'show']);
            Route::put('users/{user}', [UserController::class, 'update']);
            Route::post('users/{user}/approve', [UserController::class, 'approve']);
            Route::post('users/{user}/reject', [UserController::class, 'reject']);
            Route::post('users/{user}/suspend', [UserController::class, 'suspend']);
            Route::post('users/{user}/reactivate', [UserController::class, 'reactivate']);
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);

            Route::get('visitors', [AdminVisitorController::class, 'index']);
            Route::get('visitors/export', [AdminVisitorController::class, 'export']);
            Route::get('visitors/{visitor}', [AdminVisitorController::class, 'show']);
            Route::post('visitors/{visitor}/cancel', [AdminVisitorController::class, 'cancel']);

            Route::get('facilities', [AdminFacilityController::class, 'index']);
            Route::post('facilities', [AdminFacilityController::class, 'store']);
            Route::get('facilities/{facility}', [AdminFacilityController::class, 'show']);
            Route::post('facilities/{facility}', [AdminFacilityController::class, 'update']);
            Route::get('facilities/{facility}/blocked-slots', [AdminFacilityController::class, 'blockedSlots']);
            Route::post('facilities/{facility}/blocked-slots', [AdminFacilityController::class, 'storeBlockedSlot']);
            Route::delete('facilities/{facility}/blocked-slots/{blockedSlot}', [AdminFacilityController::class, 'destroyBlockedSlot']);

            Route::get('bookings', [AdminBookingController::class, 'index']);
            Route::get('bookings/export', [AdminBookingController::class, 'export']);
            Route::get('bookings/{booking}', [AdminBookingController::class, 'show']);
            Route::post('bookings/{booking}/approve', [AdminBookingController::class, 'approve']);
            Route::post('bookings/{booking}/reject', [AdminBookingController::class, 'reject']);
            Route::post('bookings/{booking}/cancel', [AdminBookingController::class, 'cancel']);

            Route::get('notifications', [AdminNotificationController::class, 'index']);
            Route::post('notifications', [AdminNotificationController::class, 'store']);
            Route::post('notifications/{notification}/publish', [AdminNotificationController::class, 'publish']);
            Route::post('notifications/{notification}/archive', [AdminNotificationController::class, 'archive']);

            Route::get('settings', [SettingsController::class, 'index']);
            Route::put('settings', [SettingsController::class, 'update']);

            Route::get('properties', [AuthController::class, 'properties']);
            Route::get('properties/{property}/blocks', [AuthController::class, 'blocks']);
            Route::get('blocks/{block}/units', [AuthController::class, 'units']);
        });
    });
});
