<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ItineraryController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\IncludesController;
use App\Http\Controllers\Api\ExcludesController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

// Public package routes
Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{package}', [PackageController::class, 'show']);

Route::get('/show-reviews', [ReviewController::class, 'showReviews']);






/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Logged-in user
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Booking
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::get('/my-bookings', [BookingController::class, 'userBookings']);
    Route::get('/booking-details/{id}', [BookingController::class, 'userBookingDetails']);
    Route::patch('/bookings/{booking}/cancel-booking', [BookingController::class, 'cancelBooking']);

    Route::get('/view-users/{id}', [UserController::class, 'show']);
    Route::get('/itinerary/{package}', [ItineraryController::class, 'show']);
    Route::get('/gallery/{package}', [GalleryController::class, 'show']);
    Route::get('/includes/{package}', [IncludesController::class, 'show']);
    Route::get('/excludes/{package}', [ExcludesController::class, 'show']);
    Route::get('/view-users', [UserController::class, 'index']);

    //Review endpoints
    Route::post('/reviews', [ReviewController::class, 'storeReview']);
    Route::get('/view-reviews', [ReviewController::class, 'index']);
    
   
});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Package Management
    Route::post('/packages', [PackageController::class, 'store']);
    Route::get('/adminpackages', [PackageController::class, 'adminIndex']);
    Route::put('/packages/{package}', [PackageController::class, 'update']);
    Route::delete('/packages/{package}', [PackageController::class, 'destroy']);
    Route::put('/packages/{package}/toggle-status', [PackageController::class, 'toggleStatus']);

    //itinerary management
    Route::post('/itinerary/{package}', [ItineraryController::class, 'store']);
    Route::put('/itinerary/{package}', [ItineraryController::class, 'update']);
    Route::delete('/itinerary/{package}', [ItineraryController::class, 'destroy']);

    //Gallery Management
    Route::post('/gallery/{package}', [GalleryController::class, 'add']);
    Route::put('/gallery/{package}', [GalleryController::class, 'update']);
    Route::delete('/gallery/{package}', [GalleryController::class, 'delete']);

    //Includes Management
    Route::post('/includes/{package}', [IncludesController::class, 'store']);
    Route::put('/includes/{package}', [IncludesController::class, 'update']);
    Route::delete('/includes/{package}', [IncludesController::class, 'destroy']);

    //Excludes Management
    Route::post('/excludes/{package}', [ExcludesController::class, 'store']);
    Route::put('/excludes/{package}', [ExcludesController::class, 'update']);
    Route::delete('/excludes/{package}', [ExcludesController::class, 'destroy']);

    //user management
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::patch('/bookings/{booking}/update-status', [BookingController::class, 'updateStatus']);

    //Review Management
    Route::patch('/reviews/{review}/toggle-status',[ReviewController::class, 'togglestatus']);


});