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
        $tables = ['material_requests', 'purchase_requests', 'purchase_orders'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('current_approval_level')->default(0)->after('status');
                $table->integer('max_approval_level')->default(1)->after('current_approval_level');
                $table->boolean('is_fully_approved')->default(false)->after('max_approval_level');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['material_requests', 'purchase_requests', 'purchase_orders'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['current_approval_level', 'max_approval_level', 'is_fully_approved']);
            });
        }
    }
};
