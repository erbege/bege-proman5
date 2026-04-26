<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Performance optimization: Add indexes for frequently searched/filtered columns
     */
    public function up(): void
    {
        // Materials - searched by name, code, filtered by category, is_active
        Schema::table('materials', function (Blueprint $table) {
            $table->index(['category', 'is_active'], 'materials_category_active_idx');
            $table->index('name', 'materials_name_idx');
        });

        // Suppliers - searched by name, code
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('name', 'suppliers_name_idx');
            $table->index('is_active', 'suppliers_active_idx');
        });

        // Clients - searched by name, code
        Schema::table('clients', function (Blueprint $table) {
            $table->index('name', 'clients_name_idx');
            $table->index('is_active', 'clients_active_idx');
        });

        // Inventory - filtered by project_id, joined with material
        Schema::table('inventories', function (Blueprint $table) {
            $table->index(['project_id', 'material_id'], 'inventories_project_material_idx');
        });

        // Inventory Logs - filtered by inventory_id, sorted by created_at
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->index(['inventory_id', 'created_at'], 'inventory_logs_inv_created_idx');
        });

        // Material Requests - filtered by project_id, status
        Schema::table('material_requests', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'material_requests_project_status_idx');
        });

        // Purchase Requests - filtered by project_id, status
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'purchase_requests_project_status_idx');
        });

        // Purchase Orders - filtered by project_id, status, supplier_id
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'purchase_orders_project_status_idx');
        });

        // Goods Receipts - filtered by project_id, purchase_order_id
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->index(['project_id', 'purchase_order_id'], 'goods_receipts_project_po_idx');
        });

        // Material Usages - filtered by project_id, usage_date
        Schema::table('material_usages', function (Blueprint $table) {
            $table->index(['project_id', 'usage_date'], 'material_usages_project_date_idx');
        });

        // Progress Reports - filtered by project_id, report_date
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->index(['project_id', 'report_date'], 'progress_reports_project_date_idx');
        });

        // Project Schedules - filtered by project_id, week_number
        Schema::table('project_schedules', function (Blueprint $table) {
            $table->index(['project_id', 'week_number'], 'project_schedules_project_week_idx');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex('materials_category_active_idx');
            $table->dropIndex('materials_name_idx');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('suppliers_name_idx');
            $table->dropIndex('suppliers_active_idx');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_name_idx');
            $table->dropIndex('clients_active_idx');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex('inventories_project_material_idx');
        });

        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->dropIndex('inventory_logs_inv_created_idx');
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropIndex('material_requests_project_status_idx');
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropIndex('purchase_requests_project_status_idx');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('purchase_orders_project_status_idx');
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropIndex('goods_receipts_project_po_idx');
        });

        Schema::table('material_usages', function (Blueprint $table) {
            $table->dropIndex('material_usages_project_date_idx');
        });

        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropIndex('progress_reports_project_date_idx');
        });

        Schema::table('project_schedules', function (Blueprint $table) {
            $table->dropIndex('project_schedules_project_week_idx');
        });
    }
};
