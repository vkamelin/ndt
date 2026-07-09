<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->foreignId('title_id')
                ->nullable()
                ->constrained('titles')
                ->restrictOnDelete();
            $table->foreignId('drawing_id')
                ->nullable()
                ->constrained('drawings')
                ->restrictOnDelete();
            $table->foreignId('line_id')
                ->nullable()
                ->constrained('lines')
                ->restrictOnDelete();
            $table->string('weld_number');
            $table->decimal('diameter', 10, 2)->nullable();
            $table->decimal('thickness', 10, 2)->nullable();
            $table->foreignId('material_1_id')
                ->nullable()
                ->constrained('materials')
                ->restrictOnDelete();
            $table->foreignId('material_2_id')
                ->nullable()
                ->constrained('materials')
                ->restrictOnDelete();
            $table->date('welded_at')->nullable();
            $table->foreignId('welding_process_id')
                ->nullable()
                ->constrained('welding_processes')
                ->restrictOnDelete();
            $table->foreignId('weld_type_id')
                ->nullable()
                ->constrained('weld_types')
                ->restrictOnDelete();
            $table->foreignId('pipeline_category_id')
                ->nullable()
                ->constrained('pipeline_categories')
                ->restrictOnDelete();
            $table->foreignId('medium_id')
                ->nullable()
                ->constrained('media')
                ->restrictOnDelete();
            $table->boolean('pwht')->nullable();
            $table->foreignId('normative_document_id')
                ->nullable()
                ->constrained('normative_documents')
                ->restrictOnDelete();
            $table->string('status')->default('created')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['object_id', 'drawing_id', 'line_id', 'weld_number'], 'welds_object_drawing_line_number_index');
            $table->index('normative_document_id');
        });

        Schema::create('weld_welders', function (Blueprint $table): void {
            $table->foreignId('weld_id')
                ->constrained('welds')
                ->cascadeOnDelete();
            $table->foreignId('welder_id')
                ->constrained('welders')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['weld_id', 'welder_id']);
        });

        Schema::create('weld_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('weld_id')
                ->constrained('welds')
                ->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['weld_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weld_status_history');
        Schema::dropIfExists('weld_welders');
        Schema::dropIfExists('welds');
    }
};
