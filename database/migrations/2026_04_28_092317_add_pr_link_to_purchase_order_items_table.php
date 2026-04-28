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
        // 1. Update Purchase Request Items to track ordered quantity (ordered in PO)
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->decimal('ordered_quantity', 15, 4)->default(0)->after('quantity');
        });

        // 2. Link Purchase Order Items back to Purchase Request Items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('purchase_request_item_id')
                ->nullable()
                ->after('material_id')
                ->constrained('purchase_request_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_request_item_id');
        });

        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn('ordered_quantity');
        });
    }
};
