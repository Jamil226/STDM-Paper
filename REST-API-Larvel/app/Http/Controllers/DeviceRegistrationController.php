<?php

namespace App\Http\Controllers;

use App\Models\Product; // Keep if you have product routes
use App\Models\RegisteredDevice; // Ensure this is imported
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// No longer needed for hashing S, but can be for other passwords

class DeviceRegistrationController extends Controller
{
    /**
     * Handle the secure device registration request.
     * Updated to store 'S' directly (which is then encrypted by the model).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // 1. Validate the request
        $request->validate([
            'device_id' => 'required|string|max:255',
            'timestamp_device' => 'required|integer', // Unix timestamp from device
        ]);

        $deviceId = $request->input('device_id');
        $timestampDevice = $request->input('timestamp_device');

        // Generate a cryptographically secure random secret token
        // THIS IS THE RAW SECRET THAT WILL BE SHARED WITH THE DEVICE AND ENCRYPTED IN DB
        $secretTokenS =  Str::random(64);  // Example: a 64-character long random string

        try {
            // Store or update the device with the generated secret token.
            // The 'encrypted' cast in the model will automatically encrypt $secretTokenS before saving.
            $registeredDevice = RegisteredDevice::updateOrCreate(
                ['device_id' => $deviceId],
                [
                    'secret_token' => $secretTokenS, // No Hash::make() here!
                    'last_seen_at' => now(),
                ]
            );

            // Log for debugging (optional)
            Log::info("DeviceID: {$deviceId} registered/updated successfully.");

            // 2. Respond to the device with the secret token and server timestamp
            return response()->json([
                'status' => 'success',
                'message' => 'Device registered successfully',
                'device_id' => $deviceId,
                'secret_token_s' => $secretTokenS, // Send the RAW secret back to the device
                'timestamp_server' => now()->timestamp,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Device registration failed for {$deviceId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Device registration failed.'], 500);
        }
    }
}
