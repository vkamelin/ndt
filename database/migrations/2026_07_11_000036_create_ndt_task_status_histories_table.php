<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndt_task_status_histories', function (Blueprint $table): void {
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
        Schema::dropIfExists('ndt_task_status_histories');
    }
};
