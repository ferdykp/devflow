<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('github_delivery_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_id', 100);
            $table->foreign('delivery_id')->references('delivery_id')->on('github_deliveries')->cascadeOnDelete();
            $table->foreignId('repository_id')->nullable()->constrained('github_repositories')->nullOnDelete();
            $table->unsignedBigInteger('repository_key');
            $table->string('status', 16)->default('queued');
            $table->unsignedTinyInteger('recovery_attempts')->default(0);
            $table->timestamp('last_dispatched_at')->nullable();
            $table->timestamps();
            $table->unique(['delivery_id', 'repository_key']);
            $table->index(['status', 'last_dispatched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_delivery_jobs');
    }
};
