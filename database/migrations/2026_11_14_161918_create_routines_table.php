<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('frequency')->default('daily');
            $table->json('days_of_week')->nullable();
            $table->unsignedTinyInteger('target_per_week')->default(7);
            $table->time('preferred_time')->nullable();
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->string('minimum_action', 200)->nullable();
            $table->string('normal_action', 200)->nullable();
            $table->string('extended_action', 200)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routines');
    }
};
