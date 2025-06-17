<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Models\RegisteredDevice;
use App\Models\TelemetryMessage; // Don't forget to import!
use Illuminate\Support\Str; // For generating nonces if needed, but client sends N_T

// Import the job class (will create this in Part 4)
use App\Jobs\ProcessTelemetryMessage;

class TelemetryController extends Controller
{
    // Define session token and timestamp tolerance globally or in config
    protected $sessionTokenExpirySeconds;
    protected $timestampToleranceSeconds;

    public function __construct()
    {
        // You can move these to config/services.php or a dedicated config file
        $this->sessionTokenExpirySeconds = config('services.device_registration.session_token_expiry_seconds', 3600); // 1 hour default
        $this->timestampToleranceSeconds = config('services.device_registration.timestamp_tolerance_seconds', 300); // 5 minutes default
    }

    /**
     * Handle incoming telemetry data from a device.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validate incoming request data
        $request->validate([
            'device_id' => 'required|string|max:255',
            'session_token' => 'required|string', // T_s
            'payload' => 'required|string', // The actual telemetry data (e.g., JSON string)
            'nonce_telemetry' => 'required|string|max:255', // N_T
            'timestamp_telemetry' => 'required|integer', // T_3
            'hmac' => 'required|string', // HMAC from the client
        ]);

        $deviceId = $request->input('device_id');
        $sessionToken = $request->input('session_token');
        $payload = $request->input('payload');
        $nonceTelemetry = $request->input('nonce_telemetry'); // N_T
        $timestampTelemetry = $request->input('timestamp_telemetry'); // T_3
        $hmacClient = $request->input('hmac');

        // --- Server Verification Steps ---

        // 1. Retrieve the registered device and its secret token S
        $registeredDevice = RegisteredDevice::where('device_id', $deviceId)->first();

        if (!$registeredDevice) {
            Log::warning("Telemetry failed for unknown DeviceID: {$deviceId}. Device not registered.");
            return response()->json([
                'message' => 'Device not registered or found.',
                'code' => 'DEVICE_NOT_FOUND'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // The secret_token is automatically decrypted by Laravel due to the 'encrypted' cast
        $secretTokenS = $registeredDevice->secret_token;

        // 2. Decrypt and Validate Session Token (T_s)
        try {
            $sessionTokenPayload = Crypt::decryptString($sessionToken);
            list($tokenDeviceId, $tokenTimestampServer, $tokenNonceServer) = explode('|', $sessionTokenPayload);

            // Verify DeviceID extracted from token matches request DeviceID
            if ($tokenDeviceId !== $deviceId) {
                Log::warning("Telemetry failed for DeviceID: {$deviceId}. Session token DeviceID mismatch. Token DeviceID: {$tokenDeviceId}");
                return response()->json([
                    'message' => 'Invalid session token: Device ID mismatch.',
                    'code' => 'SESSION_TOKEN_DEVICE_ID_MISMATCH'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Verify Session Token Expiry (T_2 + expiry vs. current time)
            $currentTime = now()->timestamp;
            if (($currentTime - $tokenTimestampServer) > $this->sessionTokenExpirySeconds) {
                Log::warning("Telemetry failed for DeviceID: {$deviceId}. Session token expired. Token T2: {$tokenTimestampServer}, Current T: {$currentTime}");
                return response()->json([
                    'message' => 'Session token expired. Please re-authenticate.',
                    'code' => 'SESSION_TOKEN_EXPIRED'
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::warning("Telemetry failed for DeviceID: {$deviceId}. Invalid session token payload: " . $e->getMessage());
            return response()->json([
                'message' => 'Invalid session token format.',
                'code' => 'INVALID_SESSION_TOKEN'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            Log::error("Telemetry session token processing error for DeviceID: {$deviceId}: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to process session token.',
                'code' => 'SESSION_TOKEN_PROCESSING_ERROR'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 3. Verify Telemetry Timestamp (T_3)
        $timeDifference = abs($currentTime - $timestampTelemetry);
        if ($timeDifference > $this->timestampToleranceSeconds) {
            Log::warning("Telemetry failed for DeviceID: {$deviceId}. Telemetry timestamp out of tolerance. Device T3: {$timestampTelemetry}, Server T: {$currentTime}");
            return response()->json([
                'message' => 'Telemetry timestamp out of synchronization. Please check device time.',
                'code' => 'TIMESTAMP_MISMATCH'
            ], Response::HTTP_BAD_REQUEST);
        }

        // 4. Verify HMAC from client
        // Data for HMAC: DeviceID || Payload || N_T || T_3
        $hmacDataToVerify = $deviceId . $payload . $nonceTelemetry . $timestampTelemetry;
        $serverComputedHmac = hash_hmac('sha256', $hmacDataToVerify, $secretTokenS);

        if (!hash_equals($serverComputedHmac, $hmacClient)) {
            Log::warning("Telemetry failed for DeviceID: {$deviceId}. HMAC mismatch.");
            return response()->json([
                'message' => 'Invalid telemetry HMAC.',
                'code' => 'HMAC_MISMATCH'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // If we reach here, all verifications passed. Telemetry is valid.

        // --- Part 4: Process or Enqueue Telemetry ---

        // Save the raw telemetry message to the database with 'pending' status
        $telemetryMessage = TelemetryMessage::create([
            'device_id' => $deviceId,
            'payload' => $payload,
            'nonce_telemetry' => $nonceTelemetry,
            'timestamp_telemetry' => $timestampTelemetry,
            'status' => 'pending', // Set initial status
        ]);

        // Dispatch a job to process the telemetry asynchronously
        // This is where you implement your "priority" or "enqueue" logic.
        // For simplicity, we'll just queue all telemetry for processing.
        dispatch(new ProcessTelemetryMessage($telemetryMessage->id)); // Pass the ID to retrieve in job

        Log::info("Telemetry received and queued for DeviceID: {$deviceId}. Message ID: {$telemetryMessage->id}");

        return response()->json([
            'message' => 'Telemetry received and queued for processing.',
            'status' => 'success',
            'telemetry_message_id' => $telemetryMessage->id,
        ], Response::HTTP_ACCEPTED); // 202 Accepted
    }
}
