<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('conclusions', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->index();
            $table->date('date')->index();
            $table->foreignId('object_id')->constrained('objects')->restrictOnDelete();
            $table->foreignId('ndt_method_id')->constrained('ndt_methods')->restrictOnDelete();
            $table->foreignId('ndt_request_id')->nullable()->constrained('ndt_requests')->nullOnDelete();
            $table->foreignId('prepared_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('checked_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('approved_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status')->index();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['object_id', 'number', 'date', 'status'], 'conclusions_object_number_date_status_index');
            $table->index(['object_id', 'ndt_method_id'], 'conclusions_object_method_index');
        });

        Schema::create('conclusion_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conclusion_id')->constrained('conclusions')->cascadeOnDelete();
            $table->foreignId('ndt_result_id')->constrained('ndt_results')->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(1);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['conclusion_id', 'ndt_result_id'], 'conclusion_items_unique');
            $table->index(['conclusion_id', 'sort_order'], 'conclusion_items_conclusion_sort_index');
            $table->index('ndt_result_id');
        });

        Schema::create('conclusion_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conclusion_id')->constrained('conclusions')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('basis');
            $table->string('status')->index();
            $table->timestamps();

            $table->unique(['conclusion_id', 'version_number'], 'conclusion_versions_unique');
            $table->index(['conclusion_id', 'status'], 'conclusion_versions_conclusion_status_index');
            $table->index('created_by_user_id');
        });

        Schema::create('conclusion_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conclusion_id')->constrained('conclusions')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('attached_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['conclusion_id', 'file_id'], 'conclusion_files_unique');
            $table->index('attached_by_user_id');
        });

        Schema::create('conclusion_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conclusion_id')->constrained('conclusions')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['conclusion_id', 'created_at'], 'conclusion_status_history_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusion_status_history');
        Schema::dropIfExists('conclusion_files');
        Schema::dropIfExists('conclusion_versions');
        Schema::dropIfExists('conclusion_items');
        Schema::dropIfExists('conclusions');
    }
};
