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
        if (Schema::hasTable('routine_check_ins')) {
            return;
        }

        Schema::create('routine_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->date('occurred_on');
            $table->string('status');
            $table->string('activity_level')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['routine_id', 'occurred_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The earlier check-in migration owns this table and its data.
    }
};
