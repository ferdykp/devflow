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
        Schema::table('scope_baselines', function (Blueprint $table) {
            $table->foreignId('prd_version_id')->nullable()->after('project_id')->constrained('prd_versions')->nullOnDelete();
            $table->text('constraints')->nullable()->after('out_of_scope');
            $table->text('assumptions')->nullable()->after('constraints');
            $table->text('success_criteria')->nullable()->after('assumptions');
            $table->text('definition_of_done')->nullable()->after('success_criteria');
        });

        Schema::table('scope_reviews', function (Blueprint $table) {
            $table->foreignId('prd_version_id')->nullable()->after('project_id')->constrained('prd_versions')->nullOnDelete();
        });

        Schema::table('scope_decisions', function (Blueprint $table) {
            $table->foreignId('prd_version_id')->nullable()->after('scope_review_id')->constrained('prd_versions')->nullOnDelete();
            $table->text('evidence')->nullable()->after('reason');
        });

        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scope_review_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_prd_version_id')->nullable()->constrained('prd_versions')->nullOnDelete();
            $table->foreignId('draft_prd_version_id')->nullable()->constrained('prd_versions')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('proposed');
            $table->text('decision_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_requests');

        Schema::table('scope_decisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prd_version_id');
            $table->dropColumn('evidence');
        });

        Schema::table('scope_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prd_version_id');
        });

        Schema::table('scope_baselines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prd_version_id');
            $table->dropColumn(['constraints', 'assumptions', 'success_criteria', 'definition_of_done']);
        });
    }
};
