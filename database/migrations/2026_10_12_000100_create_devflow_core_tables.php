<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->text('description')->nullable(); $table->text('goal')->nullable();
            $table->string('status')->default('active'); $table->string('priority')->default('normal');
            $table->date('target_date')->nullable(); $table->timestamps();
            $table->index(['workspace_id','status']);
        });
        Schema::create('project_notes', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); $table->string('title'); $table->longText('content')->nullable(); $table->timestamps();
        });
        Schema::create('milestones', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->text('description')->nullable(); $table->date('due_date')->nullable(); $table->timestamps();
        });
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->text('description')->nullable(); $table->string('status')->default('todo');
            $table->string('priority')->default('normal'); $table->unsignedInteger('weight')->default(1);
            $table->date('due_date')->nullable(); $table->timestamp('completed_at')->nullable(); $table->timestamps();
            $table->index(['project_id','status']);
        });
        Schema::create('backlog_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->text('description')->nullable(); $table->string('bucket')->default('future');
            $table->string('status')->default('proposed'); $table->string('source')->default('manual'); $table->timestamps();
        });
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->timestamps();
        });
        Schema::create('prd_versions', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number'); $table->string('status')->default('draft');
            $table->longText('content_markdown'); $table->string('content_hash',64)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('approved_at')->nullable();
            $table->timestamps(); $table->unique(['project_document_id','version_number']);
        });
        Schema::create('scope_baselines', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->text('in_scope')->nullable(); $table->text('out_of_scope')->nullable(); $table->timestamps();
            $table->unique('project_id');
        });
        Schema::create('scope_reviews', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->text('description')->nullable(); $table->string('classification')->default('not_assessable');
            $table->string('status')->default('pending'); $table->text('reasoning')->nullable(); $table->timestamps();
        });
        Schema::create('scope_decisions', function (Blueprint $table) {
            $table->id(); $table->foreignId('scope_review_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); $table->string('decision'); $table->text('reason')->nullable(); $table->timestamps();
        });
        Schema::create('scope_events', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); $table->string('action'); $table->json('metadata')->nullable(); $table->timestamps();
        });
        Schema::create('activities', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('type'); $table->string('title'); $table->text('description')->nullable();
            $table->string('source')->default('manual'); $table->string('source_key')->nullable();
            $table->json('metadata')->nullable(); $table->timestamp('occurred_at'); $table->timestamps();
            $table->unique(['project_id','source','source_key']); $table->index(['project_id','occurred_at']);
        });
        Schema::create('github_repositories', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('github_id')->nullable(); $table->string('full_name');
            $table->string('default_branch')->default('main'); $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable(); $table->timestamps(); $table->unique(['project_id','full_name']);
        });
        Schema::create('github_deliveries', function (Blueprint $table) {
            $table->id(); $table->string('delivery_id')->unique(); $table->string('event');
            $table->string('status')->default('received'); $table->timestamps();
        });
        Schema::create('focus_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete(); $table->date('focus_date');
            $table->timestamps(); $table->unique(['user_id','task_id','focus_date']);
        });
    }
    public function down(): void
    {
        foreach (['focus_items','github_deliveries','github_repositories','activities','scope_events','scope_decisions','scope_reviews','scope_baselines','prd_versions','project_documents','backlog_items','tasks','milestones','project_notes','projects','workspaces'] as $table) Schema::dropIfExists($table);
    }
};
