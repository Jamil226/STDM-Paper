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
            // Change 'string' to 'text' for the secret_token column
            // Ensure you use ->change() to alter an existing column
            $table->text('secret_token')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registered_devices', function (Blueprint $table) {
            // When rolling back, change it back to its original string length
            // Use a reasonable string length that was previously defined, e.g., 255
            $table->string('secret_token', 255)->change();
        });
    }
};
