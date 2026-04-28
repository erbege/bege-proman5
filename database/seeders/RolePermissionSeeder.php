<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Projects
            'projects.view',
            'projects.view.all',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.manage-team',

            // RAB
            'rab.view',
            'rab.create',
            'rab.update',
            'rab.delete',
            'rab.import',
            'rab.analyze', // AI Analysis

            // Schedule
            'schedule.view',
            'schedule.update',

            // Materials
            'materials.view',
            'materials.create',
            'materials.update',
            'materials.delete',

            // Inventory
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.adjust',

            // Suppliers
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',

            // Material Request
            'mr.view',
            'mr.manage', // Replaces create/update/delete in some places
            'mr.approve',

            // Purchase Request
            'pr.view',
            'pr.create',
            'pr.update',
            'pr.approve',
            'pr.import', // Import MR to PR

            // Purchase Order
            'po.view',
            'po.create',
            'po.update',
            'po.approve',
            'po.import', // Import PR to PO

            // Goods Receipt
            'gr.view',
            'gr.create',
            'gr.approve',

            // Procurement General
            'procurement.view',
            'procurement.manage',

            // Material Usage
            'usage.view',
            'usage.create',

            // Progress Reports
            'progress.view',
            'progress.create',
            'progress.update',
            'progress.delete',

            // Dashboard & Reports
            'dashboard.view',
            'reports.export',
            'weekly_report.view',
            'weekly_report.manage',
            'weekly_report.publish',

            // Settings
            'settings.view',
            'settings.update',
            'users.manage',
            'roles.manage',

            // Financials
            'financials.view',
            'financials.view-report',
            'financials.manage',

            // Analysis
            'analysis.view',
            'analysis.manage',
            'analysis.run-ai',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions

        // Superadmin - all permissions
        $superadmin = Role::findOrCreate('Superadmin');
        $superadmin->syncPermissions(Permission::all());

        // Alias for Superadmin to maintain compatibility if needed, but we'll use 'Superadmin' primarily
        Role::findOrCreate('super-admin')->syncPermissions(Permission::all());
        Role::findOrCreate('administrator')->syncPermissions(Permission::all());

        // Project Manager
        $projectManager = Role::findOrCreate('project-manager');
        $projectManager->givePermissionTo([
            'projects.view',
            'projects.view.all',
            'projects.create',
            'projects.update',
            'projects.manage-team',
            'rab.view',
            'rab.create',
            'rab.update',
            'rab.import',
            'rab.analyze',
            'schedule.view',
            'schedule.update',
            'materials.view',
            'inventory.view',
            'suppliers.view',
            'mr.view',
            'mr.manage',
            'mr.approve',
            'pr.view',
            'pr.approve',
            'po.view',
            'po.approve',
            'gr.view',
            'gr.create',
            'gr.approve',
            'procurement.view',
            'procurement.manage',
            'usage.view',
            'progress.view',
            'progress.update',
            'dashboard.view',
            'reports.view',
            'reports.export',
            'financials.view',
            'financials.view-report',
            'financials.manage',
            'analysis.view',
            'analysis.manage',
            'analysis.run-ai',
            'weekly_report.view',
            'weekly_report.manage',
            'weekly_report.publish',
        ]);

        // Site Manager
        $siteManager = Role::findOrCreate('site-manager');
        $siteManager->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'inventory.view',
            'mr.view',
            'mr.create',
            'mr.update',
            'pr.view',
            'usage.view',
            'usage.create',
            'progress.view',
            'progress.create',
            'progress.update',
            'dashboard.view',
        ]);

        // Logistics
        $logistics = Role::findOrCreate('logistics');
        $logistics->givePermissionTo([
            'projects.view',
            'materials.view',
            'materials.create',
            'materials.update',
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.adjust',
            'suppliers.view',
            'mr.view',
            'pr.view',
            'po.view',
            'gr.view',
            'gr.create',
            'usage.view',
            'procurement.view',
            'dashboard.view',
            'projects.view.all',
        ]);

        // Purchasing
        $purchasing = Role::findOrCreate('purchasing');
        $purchasing->givePermissionTo([
            'projects.view',
            'materials.view',
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'mr.view',
            'pr.view',
            'pr.create',
            'pr.update',
            'po.view',
            'po.create',
            'po.update',
            'gr.view',
            'procurement.view',
            'dashboard.view',
            'reports.view',
            'financials.view',
            'projects.view.all',
        ]);

        // Estimator (Quantity Surveyor)
        $estimator = Role::findOrCreate('estimator');
        $estimator->givePermissionTo([
            'projects.view',
            'projects.view.all',
            'rab.view',
            'rab.create',
            'rab.update',
            'rab.import',
            'rab.analyze',
            'schedule.view',
            'schedule.update',
            'materials.view',
            'progress.view',
            'dashboard.view',
            'reports.view',
            'reports.export',
            'financials.view',
            'weekly_report.view',
        ]);

        // Engineer
        $engineer = Role::findOrCreate('engineer');
        $engineer->givePermissionTo([
            'projects.view',
            'rab.view',
            'rab.update',
            'schedule.view',
            'materials.view',
            'progress.view',
            'progress.create',
            'dashboard.view',
        ]);

        // Viewer (Read-only)
        $viewer = Role::findOrCreate('viewer');
        $viewer->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'inventory.view',
            'mr.view',
            'pr.view',
            'po.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Administrator
        $administrator = Role::findOrCreate('administrator');
        $administrator->givePermissionTo([
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.manage-team',
            'rab.view',
            'schedule.view',
            'materials.view',
            'inventory.view',
            'suppliers.view',
            'mr.view',
            'pr.view',
            'po.view',
            'gr.view',
            'usage.view',
            'progress.view',
            'dashboard.view',
            'reports.view',
            'settings.view',
            'users.manage',
        ]);

        // Architect
        $architect = Role::findOrCreate('architect');
        $architect->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Designer
        $designer = Role::findOrCreate('designer');
        $designer->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Project Admin
        $projectAdmin = Role::findOrCreate('project-admin');
        $projectAdmin->givePermissionTo([
            'projects.view',
            'projects.update',
            'rab.view',
            'rab.update',
            'schedule.view',
            'materials.view',
            'inventory.view',
            'suppliers.view',
            'mr.view',
            'mr.create',
            'pr.view',
            'po.view',
            'gr.view',
            'usage.view',
            'progress.view',
            'progress.create',
            'dashboard.view',
            'reports.view',
        ]);

        // Supervisor
        $supervisor = Role::findOrCreate('supervisor');
        $supervisor->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'usage.view',
            'usage.create',
            'progress.view',
            'progress.create',
            'dashboard.view',
        ]);

        // Quantity Surveyor
        $quantitySurveyor = Role::findOrCreate('quantity-surveyor');
        $quantitySurveyor->givePermissionTo([
            'projects.view',
            'rab.view',
            'rab.create',
            'rab.update',
            'rab.import',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
            'reports.view',
            'reports.export',
        ]);

        // Drafter
        $drafter = Role::findOrCreate('drafter');
        $drafter->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Superintendent
        $superintendent = Role::findOrCreate('superintendent');
        $superintendent->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'inventory.view',
            'mr.view',
            'mr.create',
            'usage.view',
            'usage.create',
            'progress.view',
            'progress.create',
            'progress.update',
            'dashboard.view',
        ]);

        // Tukang (Worker)
        $tukang = Role::findOrCreate('tukang');
        $tukang->givePermissionTo([
            'projects.view',
            'materials.view',
            'usage.view',
            'dashboard.view',
        ]);

        // Operator
        $operator = Role::findOrCreate('operator');
        $operator->givePermissionTo([
            'projects.view',
            'materials.view',
            'usage.view',
            'dashboard.view',
        ]);

        // HSE (Health, Safety, Environment)
        $hse = Role::findOrCreate('hse');
        $hse->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
            'reports.view',
        ]);

        // Surveyor
        $surveyor = Role::findOrCreate('surveyor');
        $surveyor->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'progress.create',
            'dashboard.view',
        ]);
    }
}
