<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_progress_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // 'weekly' or 'monthly'
            $table->unsignedBigInteger('report_id');
            $table->foreignId('rab_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('planned_weight', 10, 4)->default(0);
            $table->decimal('actual_weight', 10, 4)->default(0);
            $table->decimal('deviation', 10, 4)->default(0);
            $table->timestamps();

            $table->index(['report_type', 'report_id']);
            $table->index(['report_type', 'report_id', 'rab_item_id'], 'report_item_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_progress_snapshots');
    }
};
