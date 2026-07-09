<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('objects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')
                ->constrained('cities')
                ->restrictOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objects');
        Schema::dropIfExists('cities');
    }
};
