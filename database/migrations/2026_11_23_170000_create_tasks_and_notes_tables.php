<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('related_entity_type', 191)->nullable();
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->date('due_date');
            $table->string('status', 30)->default('PENDING');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status', 'due_date'], 'idx_tasks_assignee_due');
            $table->index(['related_entity_type', 'related_entity_id'], 'idx_tasks_related');
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->foreignId('parent_id')->nullable()->constrained('notes')->cascadeOnDelete();
            $table->text('body');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['notable_type', 'notable_id', 'parent_id'], 'idx_notes_thread');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
        Schema::dropIfExists('tasks');
    }
};
