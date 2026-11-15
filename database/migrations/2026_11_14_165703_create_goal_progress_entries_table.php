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
        if (Schema::hasTable('goal_progress_entries')) {
            return;
        }

        Schema::create('goal_progress_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_goal_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 14, 3);
            $table->text('evidence')->nullable();
            $table->date('recorded_on');
            $table->timestamps();
            $table->index(['personal_goal_id', 'recorded_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_progress_entries');
    }
};
