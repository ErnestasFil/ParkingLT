<?php

use App\Http\Controllers\Api\ParkingZone;
use App\Http\Controllers\Api\ParkingZoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::post('/parking_zone', [ParkingZoneController::class, 'store'])->name('store')->middleware('jsonFormat');
    Route::put('/parking_zone/{parking_zone}', [ParkingZoneController::class, 'update'])->name('update')->middleware('jsonFormat');
    Route::delete('/parking_zone/{parking_zone}', [ParkingZoneController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
