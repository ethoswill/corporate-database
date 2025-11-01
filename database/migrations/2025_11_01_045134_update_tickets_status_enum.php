<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old status column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        // Add the new status enum with updated values
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['unread', 'awaiting_reply', 'archived'])->default('unread')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate data back first
        DB::statement("UPDATE tickets SET status = 'open' WHERE status = 'unread'");
        DB::statement("UPDATE tickets SET status = 'open' WHERE status = 'awaiting_reply'");
        
        // Drop the new column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        // Add back the old column
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['open', 'archived'])->default('open')->after('description');
        });
    }
};
