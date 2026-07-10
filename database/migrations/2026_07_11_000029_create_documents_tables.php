<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_types');
            $table->string('number')->nullable()->index();
            $table->date('document_date');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('object_id')->nullable()->constrained('objects')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('ndt_request_id')->nullable()->constrained('ndt_requests')->nullOnDelete();
            $table->date('valid_until')->nullable();
            $table->string('status')->index();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['object_id', 'status']);
            $table->index('valid_until');
        });

        Schema::create('files', function (Blueprint $table): void {
            $table->id();
            $table->string('original_name');
            $table->string('storage_name');
            $table->string('storage_path');
            $table->string('disk');
            $table->string('mime_type', 191);
            $table->unsignedBigInteger('size');
            $table->string('hash', 64);
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('status')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['related_type', 'related_id']);
            $table->index('uploaded_by_user_id');
        });

        Schema::create('document_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('attached_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'file_id']);
            $table->index('attached_by_user_id');
        });

        Schema::create('document_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('basis');
            $table->string('status')->index();
            $table->timestamps();

            $table->unique(['document_id', 'version_number']);
            $table->index('created_by_user_id');
        });

        Schema::create('document_relations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('related_type');
            $table->unsignedBigInteger('related_id');
            $table->timestamps();

            $table->unique(['document_id', 'related_type', 'related_id'], 'document_relations_unique');
            $table->index(['related_type', 'related_id'], 'document_relations_related_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_relations');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('document_files');
        Schema::dropIfExists('files');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_types');
    }
};
