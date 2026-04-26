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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->integer('week_number');
            $table->date('period_start');
            $table->date('period_end');

            // Cover information
            $table->string('cover_title')->nullable();
            $table->foreignId('cover_image_id')->nullable()->constrained('project_files')->nullOnDelete();
            $table->string('cover_image_path')->nullable(); // For uploaded images

            // Data snapshots (JSON)
            $table->json('cumulative_data')->nullable(); // Progress data snapshot
            $table->json('detail_data')->nullable(); // Detail progress snapshot
            $table->json('documentation_ids')->nullable(); // Array of project_file IDs

            // Text content
            $table->text('activities')->nullable();
            $table->text('problems')->nullable();

            // Status
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Unique constraint: one report per week per project
            $table->unique(['project_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
