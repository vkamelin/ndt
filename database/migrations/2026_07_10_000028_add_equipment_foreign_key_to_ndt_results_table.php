<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ndt_results', function (Blueprint $table): void {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ndt_results', function (Blueprint $table): void {
            $table->dropForeign(['equipment_id']);
        });
    }
};
