<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ndt_result_id')
                ->constrained('ndt_results')
                ->restrictOnDelete()
                ->unique();
            $table->foreignId('film_type_id')
                ->nullable()
                ->constrained('film_types')
                ->restrictOnDelete();
            $table->string('barcode')->nullable()->index();
            $table->string('conclusion_number')->nullable()->index();
            $table->date('control_date')->nullable()->index();
            $table->date('conclusion_date')->nullable()->index();
            $table->string('archive_location')->nullable()->index();
            $table->text('result_text')->nullable();
            $table->text('comment')->nullable();
            $table->string('reshoot_reason')->nullable()->index();
            $table->string('status')->default('assigned')->index();
            $table->datetime('decoded_at')->nullable()->index();
            $table->datetime('sent_to_analysis_at')->nullable()->index();
            $table->datetime('included_in_conclusion_at')->nullable()->index();
            $table->datetime('archived_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ndt_result_id', 'status'], 'rt_results_result_status_index');
        });

        Schema::create('rt_films', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rt_result_id')
                ->constrained('rt_results')
                ->cascadeOnDelete();
            $table->foreignId('film_type_id')
                ->nullable()
                ->constrained('film_types')
                ->restrictOnDelete();
            $table->string('barcode')->nullable()->index();
            $table->unsignedInteger('position_number')->nullable()->index();
            $table->unsignedInteger('exposure_count')->default(0);
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['rt_result_id', 'position_number'], 'rt_films_result_position_index');
        });

        Schema::create('rt_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rt_film_id')
                ->constrained('rt_films')
                ->cascadeOnDelete();
            $table->foreignId('file_id')
                ->nullable()
                ->constrained('files')
                ->nullOnDelete();
            $table->unsignedInteger('sequence_number')->default(1);
            $table->dateTime('captured_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['rt_film_id', 'sequence_number'], 'rt_images_film_sequence_index');
        });

        Schema::create('rt_exposures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rt_result_id')
                ->constrained('rt_results')
                ->cascadeOnDelete();
            $table->foreignId('rt_film_id')
                ->constrained('rt_films')
                ->cascadeOnDelete();
            $table->unsignedInteger('exposure_number')->default(1);
            $table->dateTime('exposed_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['rt_film_id', 'exposure_number'], 'rt_exposures_film_number_index');
            $table->index(['rt_result_id', 'exposed_at'], 'rt_exposures_result_date_index');
        });

        Schema::create('rt_reshoots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rt_result_id')
                ->constrained('rt_results')
                ->cascadeOnDelete();
            $table->foreignId('rt_film_id')
                ->nullable()
                ->constrained('rt_films')
                ->nullOnDelete();
            $table->string('reason');
            $table->dateTime('reshot_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['rt_result_id', 'reshot_at'], 'rt_reshoots_result_date_index');
        });

        Schema::create('rt_density_measurements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rt_result_id')
                ->constrained('rt_results')
                ->cascadeOnDelete();
            $table->foreignId('rt_film_id')
                ->nullable()
                ->constrained('rt_films')
                ->nullOnDelete();
            $table->decimal('density', 6, 3)->nullable();
            $table->decimal('minimum_density', 6, 3)->nullable();
            $table->decimal('maximum_density', 6, 3)->nullable();
            $table->dateTime('measured_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['rt_result_id', 'measured_at'], 'rt_density_result_date_index');
        });

        Schema::create('rt_archive_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rt_result_id')
                ->constrained('rt_results')
                ->cascadeOnDelete();
            $table->foreignId('rt_film_id')
                ->nullable()
                ->constrained('rt_films')
                ->nullOnDelete();
            $table->foreignId('file_id')
                ->nullable()
                ->constrained('files')
                ->nullOnDelete();
            $table->string('register_number')->nullable()->index();
            $table->string('archive_location')->nullable()->index();
            $table->dateTime('archived_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['rt_result_id', 'archived_at'], 'rt_archive_items_result_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_archive_items');
        Schema::dropIfExists('rt_density_measurements');
        Schema::dropIfExists('rt_reshoots');
        Schema::dropIfExists('rt_exposures');
        Schema::dropIfExists('rt_images');
        Schema::dropIfExists('rt_films');
        Schema::dropIfExists('rt_results');
    }
};
