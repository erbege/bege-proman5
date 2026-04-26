<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add region and effective_date columns to materials table
     * to sync with ahsp_base_prices
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('region_code', 10)->nullable()->after('is_active');
            $table->string('region_name', 100)->nullable()->after('region_code');
            $table->date('effective_date')->nullable()->after('region_name');
            $table->string('source', 100)->nullable()->after('effective_date');

            $table->index(['region_code', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex(['region_code', 'effective_date']);
            $table->dropColumn(['region_code', 'region_name', 'effective_date', 'source']);
        });
    }
};
