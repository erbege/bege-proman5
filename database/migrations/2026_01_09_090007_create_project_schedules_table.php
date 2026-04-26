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
        Schema::create('project_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->integer('week_number');
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('planned_weight', 8, 4)->default(0);
            $table->decimal('actual_weight', 8, 4)->default(0);
            $table->decimal('planned_cumulative', 8, 4)->default(0);
            $table->decimal('actual_cumulative', 8, 4)->default(0);
            $table->decimal('deviation', 8, 4)->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'week_number']);
            $table->index(['project_id', 'week_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_schedules');
    }
};
