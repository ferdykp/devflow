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
        if (Schema::hasTable('personal_goals')) {
            return;
        }

        Schema::create('personal_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('horizon', 20)->default('medium');
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->string('progress_mode', 20)->default('milestones');
            $table->decimal('current_value', 14, 3)->nullable();
            $table->decimal('target_value', 14, 3)->nullable();
            $table->string('unit', 40)->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedSmallInteger('review_every_days')->nullable();
            $table->date('next_review_on')->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'status', 'target_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_goals');
    }
};
