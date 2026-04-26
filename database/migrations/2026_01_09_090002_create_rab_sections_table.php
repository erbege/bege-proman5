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
        Schema::create('rab_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20); // A, B, C atau I, II, III
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('rab_sections')->nullOnDelete();
            $table->integer('level')->default(0); // 0 = main section, 1 = sub-section
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_sections');
    }
};
