<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('transfer_registers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('register_type_id')->constrained('register_types')->restrictOnDelete();
            $table->string('number')->index();
            $table->date('date')->index();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('object_id')->constrained('objects')->restrictOnDelete();
            $table->foreignId('sender_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('receiver_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status')->index();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['object_id', 'number', 'date', 'register_type_id'], 'transfer_registers_object_number_date_type_index');
            $table->index(['object_id', 'status'], 'transfer_registers_object_status_index');
        });

        Schema::create('transfer_register_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transfer_register_id')->constrained('transfer_registers')->cascadeOnDelete();
            $table->string('related_type');
            $table->unsignedBigInteger('related_id');
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(1);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['transfer_register_id', 'related_type', 'related_id'], 'transfer_register_items_unique');
            $table->index(['related_type', 'related_id'], 'transfer_register_items_related_index');
            $table->index(['transfer_register_id', 'sort_order'], 'transfer_register_items_register_sort_index');
            $table->index('file_id');
        });

        Schema::create('acts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('act_type_id')->constrained('act_types')->restrictOnDelete();
            $table->foreignId('transfer_register_id')->nullable()->constrained('transfer_registers')->nullOnDelete();
            $table->string('number')->index();
            $table->date('date')->index();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('object_id')->constrained('objects')->restrictOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['object_id', 'number', 'date'], 'acts_object_number_date_index');
            $table->index(['transfer_register_id', 'act_type_id'], 'acts_register_type_index');
        });

        Schema::create('archive_cases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transfer_register_id')->nullable()->constrained('transfer_registers')->nullOnDelete();
            $table->string('number')->index();
            $table->date('date')->index();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('object_id')->constrained('objects')->restrictOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['object_id', 'number', 'date'], 'archive_cases_object_number_date_index');
            $table->index('transfer_register_id');
        });

        Schema::create('archive_case_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archive_case_id')->constrained('archive_cases')->cascadeOnDelete();
            $table->string('related_type');
            $table->unsignedBigInteger('related_id');
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(1);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['archive_case_id', 'related_type', 'related_id'], 'archive_case_items_unique');
            $table->index(['related_type', 'related_id'], 'archive_case_items_related_index');
            $table->index(['archive_case_id', 'sort_order'], 'archive_case_items_case_sort_index');
            $table->index('file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_case_items');
        Schema::dropIfExists('archive_cases');
        Schema::dropIfExists('acts');
        Schema::dropIfExists('transfer_register_items');
        Schema::dropIfExists('transfer_registers');
    }
};
