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
        Schema::table('locations', function (Blueprint $table) {
            // Change lockup_file fields from file paths to URLs
            // No schema change needed - just keeping them as strings to store URLs instead
            // The database structure stays the same, we just change how we use them in the application
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // No rollback needed - fields remain as strings
        });
    }
};
