<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndt_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('weld_ndt_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('weld_id')
                ->constrained('welds')
                ->cascadeOnDelete();
            $table->foreignId('ndt_method_id')
                ->constrained('ndt_methods')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(['weld_id', 'ndt_method_id']);
            $table->index(['weld_id', 'ndt_method_id'], 'weld_ndt_methods_weld_id_ndt_method_id_index');
        });

        Schema::create('ndt_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('task_number')->unique();
            $table->foreignId('ndt_request_id')
                ->nullable()
                ->constrained('ndt_requests')
                ->restrictOnDelete();
            $table->foreignId('object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->foreignId('ndt_method_id')
                ->constrained('ndt_methods')
                ->restrictOnDelete();
            $table->foreignId('assignee_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
            $table->date('planned_date')->index();
            $table->string('priority')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('created')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['object_id', 'assignee_employee_id', 'status', 'planned_date'], 'ndt_tasks_object_assignee_status_planned_date_index');
            $table->index('ndt_request_id');
            $table->index('ndt_method_id');
            $table->index('assignee_employee_id');
            $table->index('object_id');
        });

        Schema::create('ndt_task_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_task_id')
                ->constrained('ndt_tasks')
                ->cascadeOnDelete();
            $table->foreignId('weld_id')
                ->constrained('welds')
                ->restrictOnDelete();
            $table->unsignedInteger('position_number');
            $table->timestamps();

            $table->unique(['ndt_task_id', 'weld_id']);
            $table->index(['ndt_task_id', 'position_number']);
            $table->index('weld_id');
        });

        Schema::create('ndt_task_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_task_id')
                ->constrained('ndt_tasks')
                ->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['ndt_task_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndt_task_status_history');
        Schema::dropIfExists('ndt_task_items');
        Schema::dropIfExists('ndt_tasks');
        Schema::dropIfExists('weld_ndt_methods');
        Schema::dropIfExists('ndt_methods');
    }
};
