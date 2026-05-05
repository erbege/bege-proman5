<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            // Fix: set default 'draft' so new records never have NULL status
            $table->string('status', 20)->default('draft')->change();

            // Auto-numbering report code
            if (!Schema::hasColumn('progress_reports', 'report_code')) {
                $table->string('report_code', 20)->nullable()->unique()->after('status');
            }

            // Rejected tracking
            if (!Schema::hasColumn('progress_reports', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('review_notes');
                $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('progress_reports', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('progress_reports', 'rejected_notes')) {
                $table->text('rejected_notes')->nullable()->after('rejected_at');
            }

            // Published tracking
            if (!Schema::hasColumn('progress_reports', 'published_by')) {
                $table->unsignedBigInteger('published_by')->nullable()->after('rejected_notes');
                $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('progress_reports', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('published_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['published_by']);
            $table->dropColumn([
                'report_code',
                'rejected_by',
                'rejected_at',
                'rejected_notes',
                'published_by',
                'published_at',
            ]);
        });
    }
};