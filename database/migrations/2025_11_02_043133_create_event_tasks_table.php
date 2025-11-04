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
        if (!Schema::hasTable('event_tasks')) {
            Schema::create('event_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calendar_event_id')->constrained()->onDelete('cascade');
                $table->foreignId('assigned_to_id')->constrained('users')->onDelete('set null')->nullable();
                $table->string('task_title');
                $table->text('task_description')->nullable();
                $table->date('due_date');
                $table->boolean('completed')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_tasks');
    }
};
