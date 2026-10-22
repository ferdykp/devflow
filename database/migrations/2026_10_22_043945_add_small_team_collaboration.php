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
        Schema::create('user_workspace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['workspace_id', 'user_id']);
        });
        Schema::create('workspace_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['workspace_id', 'email']);
        });
        Schema::create('document_task_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('prd_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_hash', 64);
            $table->json('draft_tasks');
            $table->timestamp('committed_at')->nullable();
            $table->unsignedInteger('created_count')->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'source_hash']);
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('document_task_import_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('source_index')->nullable();
            $table->unique(['document_task_import_id', 'source_index']);
        });
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropUnique(['document_task_import_id', 'source_index']);
            $table->dropConstrainedForeignId('document_task_import_id');
            $table->dropConstrainedForeignId('assignee_id');
            $table->dropColumn('source_index');
        });
        Schema::dropIfExists('document_task_imports');
        Schema::dropIfExists('workspace_invitations');
        Schema::dropIfExists('user_workspace');
    }
};
