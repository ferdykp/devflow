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
        if (Schema::hasTable('coach_recommendations')) {
            return;
        }

        Schema::create('coach_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('kind', 50);
            $table->string('title', 200);
            $table->text('explanation');
            $table->json('proposed_action')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'status', 'created_at']);
            $table->unique(['owner_id', 'kind', 'status'], 'coach_owner_kind_status_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_recommendations');
    }
};
