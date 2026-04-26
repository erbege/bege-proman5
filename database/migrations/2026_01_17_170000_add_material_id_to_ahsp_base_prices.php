<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add material_id to ahsp_base_prices to link with materials table
     * This enables bidirectional sync between materials and AHSP base prices
     */
    public function up(): void
    {
        Schema::table('ahsp_base_prices', function (Blueprint $table) {
            $table->foreignId('material_id')
                ->nullable()
                ->after('id')
                ->constrained('materials')
                ->nullOnDelete();

            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ahsp_base_prices', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropColumn('material_id');
        });
    }
};
