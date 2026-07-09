<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('organization_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->boolean('is_primary')->default(false)->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('organization_id');
        });

        Schema::create('laboratories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('organization_id');
        });

        Schema::create('welders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
            $table->string('name');
            $table->string('stamp');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('stamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welders');
        Schema::dropIfExists('laboratories');
        Schema::dropIfExists('organization_contacts');
        Schema::dropIfExists('organizations');
    }
};
