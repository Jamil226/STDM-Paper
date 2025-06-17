<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TelemetryMessage;
use Illuminate\Support\Facades\Log;

class ProcessTelemetryMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telemetryMessageId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $telemetryMessageId)
    {
        $this->telemetryMessageId = $telemetryMessageId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $telemetryMessage = TelemetryMessage::find($this->telemetryMessageId);

        if (!$telemetryMessage) {
            Log::error("ProcessTelemetryMessage: Telemetry message with ID {$this->telemetryMessageId} not found.");
            return;
        }

        try {
            // YOUR ACTUAL TELEMETRY PROCESSING LOGIC GOES HERE
            // This is where you would parse the $telemetryMessage->payload,
            // store specific data points, trigger alerts, etc.

            Log::info("Processing telemetry message ID: {$telemetryMessage->id} for DeviceID: {$telemetryMessage->device_id}");

            // Example: If payload is JSON, decode it
            // $data = json_decode($telemetryMessage->payload, true);
            // if (json_last_error() === JSON_ERROR_NONE) {
            //     Log::info("Telemetry data: " . json_encode($data));

            // } else {
            //     Log::warning("Telemetry payload is not valid JSON for message ID: {$telemetryMessage->id}");
            // }

            // Update the status and processed_at timestamp
            $telemetryMessage->status = 'processed';
            $telemetryMessage->processed_at = now();
            $telemetryMessage->save();

            Log::info("Successfully processed telemetry message ID: {$telemetryMessage->id}");
        } catch (\Exception $e) {
            // Handle any errors during processing
            $telemetryMessage->status = 'failed';
            $telemetryMessage->save();
            Log::error("Error processing telemetry message ID: {$telemetryMessage->id}: " . $e->getMessage());
            // You might want to re-queue the job or send notifications here
        }
    }
}
