<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'materials',
            'welding_processes',
            'weld_types',
            'pipeline_categories',
            'media',
            'normative_documents',
            'defect_types',
            'result_statuses',
            'register_types',
            'act_types',
            'film_types',
            'chemical_types',
        ] as $tableName) {
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
        foreach ([
            'chemical_types',
            'film_types',
            'act_types',
            'register_types',
            'result_statuses',
            'defect_types',
            'normative_documents',
            'media',
            'pipeline_categories',
            'weld_types',
            'welding_processes',
            'materials',
        ] as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
