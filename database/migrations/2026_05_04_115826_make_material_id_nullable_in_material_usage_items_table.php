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
        Schema::table('material_usage_items', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->change();
            $table->string('material_name')->nullable()->after('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_usage_items', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable(false)->change();
            $table->dropColumn('material_name');
        });
    }
};
