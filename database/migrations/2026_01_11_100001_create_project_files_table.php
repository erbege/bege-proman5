<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained('project_files')->onDelete('set null');
            $table->string('name'); // Display name
            $table->string('original_name'); // Original filename
            $table->enum('type', ['file', 'folder'])->default('file');
            $table->enum('category', ['planning', 'design', 'cad', 'document', 'image', 'other'])->default('other');
            $table->enum('status', ['draft', 'review', 'approved', 'final'])->default('draft');
            $table->unsignedInteger('current_version')->default(1);
            $table->boolean('is_final')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'folder_id']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};
