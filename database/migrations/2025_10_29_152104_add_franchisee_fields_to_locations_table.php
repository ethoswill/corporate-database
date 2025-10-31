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
            $table->string('owner_name')->nullable()->after('name');
            $table->date('studio_anniversary')->nullable()->after('owner_name');
            $table->string('lockup_file_1')->nullable()->after('studio_anniversary');
            $table->string('lockup_file_2')->nullable()->after('lockup_file_1');
            $table->string('lockup_file_3')->nullable()->after('lockup_file_2');
            $table->string('lockup_file_4')->nullable()->after('lockup_file_3');
            $table->string('lockup_file_5')->nullable()->after('lockup_file_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name',
                'studio_anniversary',
                'lockup_file_1',
                'lockup_file_2',
                'lockup_file_3',
                'lockup_file_4',
                'lockup_file_5',
            ]);
        });
    }
};
