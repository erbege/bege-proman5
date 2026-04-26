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
        Schema::create('material_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('raw_material_name'); // Nama material dari AI
            $table->decimal('estimated_qty', 15, 4)->default(0);
            $table->string('unit', 20);
            $table->decimal('coefficient', 10, 6)->default(0); // Koefisien per satuan
            $table->string('analysis_source', 20)->default('ai'); // ai, manual, sni
            $table->json('ai_response_raw')->nullable(); // Raw response dari AI
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('rab_item_id');
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_forecasts');
    }
};
