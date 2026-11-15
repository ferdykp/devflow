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
        if (Schema::hasTable('calendar_event_exceptions')) {
            return;
        }

        Schema::create('calendar_event_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->timestamp('original_starts_at');
            $table->string('action', 20);
            $table->timestamp('new_starts_at')->nullable();
            $table->timestamp('new_ends_at')->nullable();
            $table->unsignedSmallInteger('actual_duration_minutes')->nullable();
            $table->timestamps();
            $table->unique(['calendar_event_id', 'original_starts_at'], 'cal_ev_exc_occ_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_event_exceptions');
    }
};
