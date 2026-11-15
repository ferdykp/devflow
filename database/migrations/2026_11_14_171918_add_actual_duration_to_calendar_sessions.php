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
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->unsignedSmallInteger('actual_duration_minutes')->nullable();
        });

        Schema::table('learning_sessions', function (Blueprint $table): void {
            $table->unsignedSmallInteger('planned_minutes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->dropColumn('actual_duration_minutes');
        });

        Schema::table('learning_sessions', function (Blueprint $table): void {
            $table->dropColumn('planned_minutes');
        });
    }
};
