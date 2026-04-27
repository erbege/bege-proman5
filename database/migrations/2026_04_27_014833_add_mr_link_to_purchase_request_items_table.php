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
        // 1. Update Material Request Items to track ordered quantity
        Schema::table('material_request_items', function (Blueprint $table) {
            $table->decimal('ordered_quantity', 15, 4)->default(0)->after('quantity');
        });

        // 2. Link Purchase Request Items back to Material Request Items
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->foreignId('material_request_item_id')
                ->nullable()
                ->after('material_id')
                ->constrained('material_request_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('material_request_item_id');
        });

        Schema::table('material_request_items', function (Blueprint $table) {
            $table->dropColumn('ordered_quantity');
        });
    }
};
