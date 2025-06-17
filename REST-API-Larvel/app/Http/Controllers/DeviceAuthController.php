<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt; // For encryption/decryption
use Illuminate\Support\Facades\Log;
use App\Models\RegisteredDevice;

class DeviceAuthController extends Controller
{
    /**
     * Handle the challenge-response authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        // 1. Validate incoming request data
        $request->validate([
            'device_id' => 'required|string|max:255',
            'nonce_device' => 'required|string|max:255', // N_D
            'timestamp_device' => 'required|integer',     // T_1
            'hmac_client' => 'required|string',           // Client's computed HMAC
        ]);

        $deviceId = $request->input('device_id');
        $nonceDevice = $request->input('nonce_device');
        $timestampDevice = $request->input('timestamp_device'); // T_1
        $hmacClient = $request->input('hmac_client');

        $timestampTolerance = config('services.device_registration.timestamp_tolerance_seconds', 300); // 5 minutes default

        // Retrieve the registered device
        $registeredDevice = RegisteredDevice::where('device_id', $deviceId)->first();

        if (!$registeredDevice) {
            Log::warning("Authentication failed for unknown DeviceID: {$deviceId}");
            return response()->json([
                'message' => 'Device not registered.',
                'code' => 'DEVICE_NOT_FOUND'
            ], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // Get the secret token S (Laravel decrypts it automatically due to 'encrypted' cast)
        $secretTokenS = $registeredDevice->secret_token;

        // Step 4: Server verifies timestamp
        $currentTime = now()->timestamp;
        $timeDifference = abs($currentTime - $timestampDevice);

        if ($timeDifference > $timestampTolerance) {
            Log::warning("Authentication failed for DeviceID: {$deviceId}. Timestamp out of tolerance. Device T1: {$timestampDevice}, Server T: {$currentTime}");
            return response()->json([
                'message' => 'Timestamp out of synchronization. Please check device time.',
                'code' => 'TIMESTAMP_MISMATCH'
            ], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }

        // Step 4: Server verifies HMAC
        // HMAC over <DeviceID, N_D, T_1> using S
        $hmacDataToVerify = $deviceId . $nonceDevice . $timestampDevice;
        $serverComputedHmac = hash_hmac('sha256', $hmacDataToVerify, $secretTokenS);

        if (!hash_equals($serverComputedHmac, $hmacClient)) {
            Log::warning("Authentication failed for DeviceID: {$deviceId}. HMAC mismatch.");
            return response()->json([
                'message' => 'Invalid authentication credentials (HMAC mismatch).',
                'code' => 'HMAC_MISMATCH'
            ], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // If we reach here, timestamp and HMAC are valid. Device is authenticated.

        // Step 5: Server generates session token T_s = Encrypt(DeviceID || T2 || N_S)
        $timestampServer = now()->timestamp; // T2
        $nonceServer = bin2hex(random_bytes(16)); // N_S: New nonce from server

        $sessionTokenPayload = $deviceId . '|' . $timestampServer . '|' . $nonceServer;
        $sessionToken = Crypt::encryptString($sessionTokenPayload); // T_s

        // Update last seen for the device
        $registeredDevice->update(['last_seen_at' => now()]);
        Log::info("DeviceID: {$deviceId} authenticated successfully. Session token issued.");

        // Step 6: Server replies with <T_s, N_S, HMAC>
        // HMAC over <T_s, N_S> using S
        $hmacResponseData = $sessionToken . $nonceServer;
        $hmacResponse = hash_hmac('sha256', $hmacResponseData, $secretTokenS);

        return response()->json([
            'session_token' => $sessionToken, // T_s
            'nonce_server' => $nonceServer,   // N_S
            'hmac_server' => $hmacResponse,  // HMAC over T_s and N_S
            'message' => 'Authentication successful. Session token issued.'
        ], Response::HTTP_OK); // 200 OK
    }
}
