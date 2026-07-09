<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['titles', 'drawings', 'lines'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true)->index();
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['lines', 'drawings', 'titles'] as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
