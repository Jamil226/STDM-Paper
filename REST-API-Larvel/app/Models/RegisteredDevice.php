<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisteredDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'secret_token', // Changed from hashed_secret_token
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'secret_token' => 'encrypted', // This automatically encrypts/decrypts
    ];
}