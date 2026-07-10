<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_type_id')
                ->constrained('equipment_types')
                ->restrictOnDelete();
            $table->foreignId('object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->string('name');
            $table->string('inventory_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('available')->index();
            $table->date('purchased_at')->nullable();
            $table->date('write_off_at')->nullable();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['object_id', 'inventory_number', 'serial_number', 'status'], 'equipment_object_inventory_serial_status_index');
            $table->index('equipment_type_id');
            $table->index('status');
            $table->index('object_id');
        });

        Schema::create('equipment_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('verified_at');
            $table->date('valid_until')->nullable();
            $table->string('certificate_number')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'verified_at']);
            $table->index(['equipment_id', 'valid_until']);
        });

        Schema::create('equipment_calibrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('calibrated_at');
            $table->date('valid_until')->nullable();
            $table->string('certificate_number')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'calibrated_at']);
            $table->index(['equipment_id', 'valid_until']);
        });

        Schema::create('equipment_repairs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('started_at');
            $table->date('completed_at')->nullable();
            $table->text('description');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'started_at']);
        });

        Schema::create('equipment_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('issued_at');
            $table->date('returned_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'employee_id', 'issued_at']);
            $table->index(['equipment_id', 'returned_at']);
        });

        Schema::create('equipment_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('from_object_id')
                ->nullable()
                ->constrained('objects')
                ->nullOnDelete();
            $table->foreignId('to_object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('moved_at');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'moved_at']);
            $table->index(['from_object_id', 'to_object_id']);
        });

        Schema::create('equipment_defects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('detected_at');
            $table->text('description');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'detected_at']);
        });

        Schema::create('equipment_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('document_name');
            $table->string('document_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'document_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_documents');
        Schema::dropIfExists('equipment_defects');
        Schema::dropIfExists('equipment_movements');
        Schema::dropIfExists('equipment_assignments');
        Schema::dropIfExists('equipment_repairs');
        Schema::dropIfExists('equipment_calibrations');
        Schema::dropIfExists('equipment_verifications');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('equipment_types');
    }
};
