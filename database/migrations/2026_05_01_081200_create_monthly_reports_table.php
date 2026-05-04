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
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            
            // Period (e.g., 2026, 5)
            $table->integer('year');
            $table->integer('month');
            $table->date('period_start');
            $table->date('period_end');

            // Cover information
            $table->string('cover_title')->nullable();
            $table->foreignId('cover_image_id')->nullable()->constrained('project_files')->nullOnDelete();
            $table->string('cover_image_path')->nullable();

            // Data snapshots (JSON)
            $table->json('cumulative_data')->nullable(); // Progress data snapshot
            $table->json('detail_data')->nullable(); // Detail progress snapshot
            $table->json('documentation_ids')->nullable(); // Array of project_file IDs
            $table->json('documentation_uploads')->nullable(); // Array of uploaded photo paths

            // Text content
            $table->text('activities')->nullable();
            $table->text('problems')->nullable();

            // Approval Workflow Fields (similar to WeeklyReport)
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Unique constraint: one report per month per project
            $table->unique(['project_id', 'year', 'month']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
