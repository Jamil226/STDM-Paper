<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_messages', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index(); // Link to the registered_devices table
            $table->text('payload'); // The actual telemetry data (e.g., JSON string)
            $table->string('nonce_telemetry'); // N_T from the device
            $table->unsignedBigInteger('timestamp_telemetry'); // T_3 from the device
            $table->string('status')->default('pending'); // e.g., 'pending', 'queued', 'processed', 'failed'
            $table->timestamp('processed_at')->nullable();
            $table->timestamps(); // created_at, updated_at

            // Optional: Add a foreign key constraint if you want strict referential integrity
            // $table->foreign('device_id')->references('device_id')->on('registered_devices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_messages');
    }
};
