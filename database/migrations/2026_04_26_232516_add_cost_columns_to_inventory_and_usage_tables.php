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
        Schema::table('inventories', function (Blueprint $table) {
            $table->decimal('average_cost', 15, 2)->default(0)->after('quantity');
        });

        Schema::table('material_usage_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity');
            $table->decimal('total_cost', 15, 2)->default(0)->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('average_cost');
        });

        Schema::table('material_usage_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'total_cost']);
        });
    }
};
