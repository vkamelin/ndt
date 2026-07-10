<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete();
            $table->foreignId('object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->string('type')->index();
            $table->string('status')->default('open')->index();
            $table->dateTime('started_at')->index();
            $table->dateTime('finished_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'type', 'status'], 'shifts_employee_type_status_index');
            $table->index(['object_id', 'type', 'status'], 'shifts_object_type_status_index');
        });

        Schema::create('lab_shift_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete()
                ->unique();
            $table->text('summary')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('completed_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('lab_shift_regulatory_works', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->dateTime('worked_at')->index();
            $table->text('description');
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shift_id', 'worked_at'], 'lab_shift_regulatory_works_shift_date_index');
        });

        Schema::create('film_inventory_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('rt_film_id')
                ->nullable()
                ->constrained('rt_films')
                ->nullOnDelete();
            $table->string('operation')->index();
            $table->unsignedInteger('quantity')->default(1);
            $table->dateTime('transacted_at')->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shift_id', 'operation'], 'film_inventory_transactions_shift_operation_index');
        });

        Schema::create('chemical_inventory_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('chemical_type_id')
                ->nullable()
                ->constrained('chemical_types')
                ->nullOnDelete();
            $table->string('operation')->index();
            $table->unsignedInteger('quantity')->default(1);
            $table->dateTime('transacted_at')->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shift_id', 'operation'], 'chemical_inventory_transactions_shift_operation_index');
        });

        Schema::create('chemical_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('chemical_type_id')
                ->nullable()
                ->constrained('chemical_types')
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status')->default('requested')->index();
            $table->dateTime('requested_at')->index();
            $table->dateTime('closed_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shift_id', 'status'], 'chemical_requests_shift_status_index');
        });

        Schema::create('decoder_shift_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete()
                ->unique();
            $table->text('summary')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('completed_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('decoder_film_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('rt_result_id')
                ->nullable()
                ->constrained('rt_results')
                ->nullOnDelete();
            $table->string('group_name');
            $table->dateTime('viewed_at')->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shift_id', 'viewed_at'], 'decoder_film_groups_shift_date_index');
        });

        Schema::create('decoder_rejects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('rt_result_id')
                ->nullable()
                ->constrained('rt_results')
                ->nullOnDelete();
            $table->string('reason');
            $table->dateTime('recorded_at')->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('decoder_forgery_suspicions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('rt_result_id')
                ->nullable()
                ->constrained('rt_results')
                ->nullOnDelete();
            $table->string('reason');
            $table->dateTime('recorded_at')->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('decoder_cleanups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->dateTime('completed_at')->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('decoder_decryptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();
            $table->foreignId('rt_result_id')
                ->nullable()
                ->constrained('rt_results')
                ->nullOnDelete();
            $table->text('result_text')->nullable();
            $table->text('analysis_comment')->nullable();
            $table->dateTime('decrypted_at')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shift_id', 'decrypted_at'], 'decoder_decryptions_shift_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decoder_decryptions');
        Schema::dropIfExists('decoder_cleanups');
        Schema::dropIfExists('decoder_forgery_suspicions');
        Schema::dropIfExists('decoder_rejects');
        Schema::dropIfExists('decoder_film_groups');
        Schema::dropIfExists('decoder_shift_reports');
        Schema::dropIfExists('chemical_requests');
        Schema::dropIfExists('chemical_inventory_transactions');
        Schema::dropIfExists('film_inventory_transactions');
        Schema::dropIfExists('lab_shift_regulatory_works');
        Schema::dropIfExists('lab_shift_reports');
        Schema::dropIfExists('shifts');
    }
};
