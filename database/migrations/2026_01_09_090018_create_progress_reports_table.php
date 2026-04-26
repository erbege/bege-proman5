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
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rab_item_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->decimal('progress_percentage', 5, 2); // Progress increment for this report
            $table->decimal('cumulative_progress', 5, 2)->default(0); // Total progress after this report
            $table->text('description')->nullable();
            $table->text('issues')->nullable(); // Problems encountered
            $table->json('photos')->nullable(); // Array of photo paths
            $table->string('weather')->nullable(); // cuaca saat laporan
            $table->integer('workers_count')->default(0); // Jumlah pekerja
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['project_id', 'report_date']);
            $table->index(['rab_item_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};
