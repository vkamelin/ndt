<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->foreignId('position_id')
                ->constrained('positions')
                ->restrictOnDelete();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->string('personnel_number')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index('object_id');
            $table->index('position_id');
        });

        Schema::create('employee_qualifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();
            $table->string('method', 16);
            $table->date('valid_until')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'method', 'valid_until'], 'employee_qualifications_employee_method_valid_until_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_qualifications');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
    }
};
