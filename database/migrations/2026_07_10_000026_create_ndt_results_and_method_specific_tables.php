<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndt_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_task_id')
                ->constrained('ndt_tasks')
                ->restrictOnDelete();
            $table->foreignId('weld_id')
                ->constrained('welds')
                ->restrictOnDelete();
            $table->foreignId('ndt_method_id')
                ->constrained('ndt_methods')
                ->restrictOnDelete();
            $table->foreignId('executor_employee_id')
                ->constrained('employees')
                ->restrictOnDelete();
            $table->unsignedBigInteger('equipment_id')->nullable()->index();
            $table->foreignId('normative_document_id')
                ->nullable()
                ->constrained('normative_documents')
                ->restrictOnDelete();
            $table->date('control_date')->index();
            $table->datetime('analyzed_at')->nullable()->index();
            $table->text('result_text')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('created')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['weld_id', 'ndt_method_id', 'status'], 'ndt_results_weld_method_status_index');
            $table->index(['executor_employee_id']);
        });

        Schema::create('ndt_result_defects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_result_id')
                ->constrained('ndt_results')
                ->cascadeOnDelete();
            $table->foreignId('defect_type_id')
                ->nullable()
                ->constrained('defect_types')
                ->restrictOnDelete();
            $table->text('description');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['ndt_result_id', 'created_at']);
        });

        Schema::create('ndt_result_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_result_id')
                ->constrained('ndt_results')
                ->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['ndt_result_id', 'created_at']);
        });

        foreach ([
            'vt_results',
            'pt_results',
            'mt_results',
            'ut_results',
        ] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName): void {
                $table->id();
                $table->foreignId('ndt_result_id')
                    ->constrained('ndt_results')
                    ->cascadeOnDelete()
                    ->unique();
                $table->string('conclusion_number')->nullable();
                $table->date('conclusion_date')->nullable();
                $table->string('transfer_register_number')->nullable();
                $table->string('act_number')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('vt_results', function (Blueprint $table): void {
            $table->text('measurements')->nullable()->after('conclusion_date');
        });
        Schema::table('pt_results', function (Blueprint $table): void {
            $table->string('control_zone')->nullable()->after('conclusion_date');
            $table->text('materials_used')->nullable()->after('control_zone');
        });
        Schema::table('mt_results', function (Blueprint $table): void {
            $table->string('control_zone')->nullable()->after('conclusion_date');
            $table->string('material')->nullable()->after('control_zone');
            $table->text('control_parameters')->nullable()->after('material');
        });
        Schema::table('ut_results', function (Blueprint $table): void {
            $table->string('sounding_scheme')->nullable()->after('conclusion_date');
            $table->string('transducer')->nullable()->after('sounding_scheme');
            $table->text('tuning_parameters')->nullable()->after('transducer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ut_results');
        Schema::dropIfExists('mt_results');
        Schema::dropIfExists('pt_results');
        Schema::dropIfExists('vt_results');
        Schema::dropIfExists('ndt_result_status_history');
        Schema::dropIfExists('ndt_result_defects');
        Schema::dropIfExists('ndt_results');
    }
};
