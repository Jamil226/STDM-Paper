<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelemetryMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'payload',
        'nonce_telemetry',
        'timestamp_telemetry',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'timestamp_telemetry' => 'integer',
    ];

    // Define relationship if needed (e.g., a message belongs to a device)
    public function device()
    {
        return $this->belongsTo(RegisteredDevice::class, 'device_id', 'device_id');
    }
}
