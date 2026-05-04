<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add approval workflow fields to progress_reports and weekly_reports.
     *
     * Progress Report:  draft → submitted → reviewed
     * Weekly Report:    draft → in_review → approved → published (replaces old enum)
     */
    public function up(): void
    {
        // ─── Progress Reports: add lightweight review workflow ──────────
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('reported_by');
            $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');

            $table->index('status');
        });

        // ─── Weekly Reports: expand status enum + add approval columns ─
        // First, change the column type from enum to string for flexibility
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->string('status_new', 20)->default('draft')->after('problems');
        });

        // Copy existing status values
        \DB::statement("UPDATE weekly_reports SET status_new = status");

        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        Schema::table('weekly_reports', function (Blueprint $table) {
            // Approval workflow fields
            $table->foreignId('submitted_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->foreignId('reviewed_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->foreignId('approved_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'reviewed_at', 'review_notes']);
        });

        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'submitted_at', 'reviewed_at', 'approved_at', 'rejection_reason',
            ]);
        });

        // Revert status to enum
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->string('status_old', 20)->default('draft')->after('problems');
        });
        \DB::statement("UPDATE weekly_reports SET status_old = CASE WHEN status IN ('draft','published') THEN status ELSE 'draft' END");
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
};
