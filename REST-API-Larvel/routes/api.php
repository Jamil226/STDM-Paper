<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceRegistrationController;
use App\Http\Controllers\DeviceAuthController; // Import the new controller
use App\Http\Controllers\TelemetryController;
use App\Models\TelemetryMessage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Standard user authentication routes (if using Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Device registration endpoint (from previous algorithm)
Route::post('/device/register', [DeviceRegistrationController::class, 'register']);

// NEW: Device Challenge-Response Authentication Endpoint
Route::post('/device/authenticate', [DeviceAuthController::class, 'authenticate']);

Route::post('/telemetry', [TelemetryController::class, 'store'])->name('telemetry.store');
