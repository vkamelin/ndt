<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndt_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number')->unique();
            $table->date('request_date')->index();
            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('object_id')
                ->constrained('objects')
                ->restrictOnDelete();
            $table->foreignId('title_id')
                ->nullable()
                ->constrained('titles')
                ->restrictOnDelete();
            $table->string('priority')->nullable();
            $table->date('due_date')->nullable();
            $table->text('basis')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('draft')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('object_id');
            $table->index('title_id');
        });

        Schema::create('ndt_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_request_id')
                ->constrained('ndt_requests')
                ->cascadeOnDelete();
            $table->foreignId('weld_id')
                ->constrained('welds')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(['ndt_request_id', 'weld_id']);
        });

        Schema::create('ndt_request_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_request_id')
                ->constrained('ndt_requests')
                ->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['ndt_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndt_request_status_history');
        Schema::dropIfExists('ndt_request_items');
        Schema::dropIfExists('ndt_requests');
    }
};
