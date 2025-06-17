<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registered_devices', function (Blueprint $table) {
            // No direct schema change here if 'hashed_secret_token' was already string
            // This migration primarily ensures the field exists and is string.
            // The encryption handling will be in the model.
            // If you named it 'hashed_secret_token' before, rename it now
            // or modify the previous migration to directly create 'secret_token'.
            // For simplicity, assuming the column 'secret_token' already exists from the last step,
            // or was renamed from 'hashed_secret_token'.
            // If you want to rename it in this migration:
            $table->renameColumn('hashed_secret_token', 'secret_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registered_devices', function (Blueprint $table) {
            // $table->renameColumn('secret_token', 'hashed_secret_token'); // Reverse rename if done
        });
    }
};
