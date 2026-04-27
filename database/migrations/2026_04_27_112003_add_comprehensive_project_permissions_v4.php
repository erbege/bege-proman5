<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define new permissions
        $permissions = [
            // Analysis
            'analysis.view',
            'analysis.manage',
            'analysis.run-ai',
            
            // Financial & Procurement
            'financials.view',        // Can see unit prices / totals
            'financials.view-report', // Cost Control / P&L
            'financials.manage',      // Approvals / Strategic
            'procurement.view',       // List visibility
            'procurement.manage',     // PR/PO management
            
            // Material Request
            'mr.view',
            'mr.manage',
            
            // Inventory
            'inventory.view',
            'inventory.manage',
            
            // RAB
            'rab.view',
            'rab.manage',
            
            // Schedule
            'schedule.view',
            'schedule.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // 1. Superadmin & PM (Full Access)
        $fullAccessRoles = ['Superadmin', 'super-admin', 'administrator', 'project-manager'];
        foreach ($fullAccessRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // 2. Estimator / QS (RAB & Analysis Focus)
        $estimatorRoles = Role::whereIn('name', ['estimator', 'quantity-surveyor', 'project-admin'])->get();
        foreach ($estimatorRoles as $role) {
            $role->givePermissionTo([
                'analysis.view',
                'analysis.manage',
                'analysis.run-ai',
                'financials.view',
                'procurement.view',
                'procurement.manage',
                'mr.view',
                'inventory.view',
                'rab.view',
                'rab.manage',
                'schedule.view',
            ]);
        }

        // 3. Site Manager / Engineer (Operational focus)
        $siteRoles = Role::whereIn('name', ['site-manager', 'engineer', 'architect'])->get();
        foreach ($siteRoles as $role) {
            $role->givePermissionTo([
                'analysis.view',
                'rab.view',
                'schedule.view',
                'schedule.manage',
                'procurement.view',
                'procurement.manage', 
                'mr.view',
                'mr.manage',
                'inventory.view',
            ]);
        }

        // 4. Logistics / Purchasing
        $logisticsRoles = Role::whereIn('name', ['logistics', 'purchasing'])->get();
        foreach ($logisticsRoles as $role) {
            $role->givePermissionTo([
                'procurement.view',
                'procurement.manage',
                'mr.view',
                'mr.manage',
                'inventory.view',
                'inventory.manage',
                'rab.view',
                'schedule.view',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', [
            'analysis.view',
            'analysis.manage',
            'analysis.run-ai',
            'financials.view',
            'financials.view-report',
            'financials.manage',
            'procurement.view',
            'procurement.manage',
            'mr.view',
            'mr.manage',
            'inventory.view',
            'inventory.manage',
            'rab.view',
            'rab.manage',
            'schedule.view',
            'schedule.manage',
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
