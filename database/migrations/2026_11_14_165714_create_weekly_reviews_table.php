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
        if (Schema::hasTable('weekly_reviews')) {
            return;
        }

        Schema::create('weekly_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->date('week_starts_on');
            $table->text('reflection')->nullable();
            $table->json('carry_forward_task_ids')->nullable();
            $table->json('planned_commitments')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['owner_id', 'week_starts_on'], 'week_rev_owner_week_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reviews');
    }
};
