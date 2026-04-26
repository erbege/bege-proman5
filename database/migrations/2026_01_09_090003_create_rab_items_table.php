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
        Schema::create('rab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rab_section_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->nullable();
            $table->string('work_name');
            $table->text('description')->nullable();
            $table->decimal('volume', 15, 4)->default(0);
            $table->string('unit', 20); // m3, m2, ls, kg, bh
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('total_price', 18, 2)->default(0);
            $table->decimal('weight_percentage', 8, 4)->default(0); // Bobot untuk Kurva S
            $table->date('planned_start')->nullable();
            $table->date('planned_end')->nullable();
            $table->decimal('actual_progress', 5, 2)->default(0); // 0-100%
            $table->integer('sort_order')->default(0);
            $table->boolean('is_analyzed')->default(false); // Flag apakah sudah dianalisis AI
            $table->timestamps();

            $table->index(['project_id', 'rab_section_id']);
            $table->index(['planned_start', 'planned_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_items');
    }
};
