<?php

use App\Http\Controllers\Api\ParkingSpaceController;
use App\Http\Controllers\Api\ParkingZone;
use App\Http\Controllers\Api\ParkingZoneController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Reservation;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('V1')->group(function () {
    Route::get('/parking_zone', [ParkingZoneController::class, 'index'])->name('index');
    Route::get('/parking_zone/{parking_zone}', [ParkingZoneController::class, 'show'])->name('show');
    Route::post('/parking_zone', [ParkingZoneController::class, 'store'])->name('store')->middleware('jsonFormat', 'auth:api', 'role:Administrator');
    Route::put('/parking_zone/{parking_zone}', [ParkingZoneController::class, 'update'])->name('update')->middleware('jsonFormat', 'auth:api', 'role:Administrator');
    Route::delete('/parking_zone/{parking_zone}', [ParkingZoneController::class, 'destroy'])->name('destroy')->middleware('auth:api', 'role:Administrator');
    Route::prefix('/parking_zone/{parking_zone}')->group(function () {
        Route::get('/parking_space', [ParkingSpaceController::class, 'index'])->name('index');
        Route::get('/parking_space/{parking_space}', [ParkingSpaceController::class, 'show'])->name('show');
        Route::post('/parking_space', [ParkingSpaceController::class, 'store'])->name('store')->middleware('jsonFormat', 'auth:api', 'role:Administrator');
        Route::put('/parking_space/{parking_space}', [ParkingSpaceController::class, 'update'])->name('update')->middleware('jsonFormat', 'auth:api', 'role:Administrator');
        Route::delete('/parking_space/{parking_space}', [ParkingSpaceController::class, 'destroy'])->name('destroy')->middleware('auth:api', 'role:Administrator');
        Route::prefix('/parking_space/{parking_space}')->group(function () {
            Route::get('/reservation', [ReservationController::class, 'index'])->name('index')->middleware('auth:api', 'role:User,Administrator');
            Route::get('/reservation/{reservation}', [ReservationController::class, 'show'])->name('show')->middleware('auth:api', 'role:User,Administrator', 'can:view,reservation');
            Route::post('/reservation', [ReservationController::class, 'store'])->name('store')->middleware('jsonFormat', 'auth:api', 'role:User,Administrator');
            Route::patch('/reservation/{reservation}', [ReservationController::class, 'update'])->name('update')->middleware('jsonFormat', 'auth:api', 'role:User,Administrator', 'can:update,reservation');
            Route::delete('/reservation/{reservation}', [ReservationController::class, 'destroy'])->name('destroy')->middleware('jsonFormat', 'auth:api', 'role:Administrator');
        });
    });
    Route::post('/register', [UserController::class, 'register'])->name('register')->middleware('jsonFormat');
    Route::post('/login', [UserController::class, 'login'])->name('login')->middleware('jsonFormat');
    Route::post('/refresh', [UserController::class, 'refreshToken'])->name('refreshToken')->middleware('auth:api');
    Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth:api');

    Route::get('/user', [UserController::class, 'index'])->name('index')->middleware('auth:api');
});
