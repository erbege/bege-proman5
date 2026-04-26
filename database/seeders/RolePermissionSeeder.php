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
            'mr.create',
            'mr.update',
            'mr.delete',
            'mr.approve',

            // Purchase Request
            'pr.view',
            'pr.create',
            'pr.update',
            'pr.delete',
            'pr.approve',

            // Purchase Order
            'po.view',
            'po.create',
            'po.update',
            'po.delete',

            // Goods Receipt
            'gr.view',
            'gr.create',

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
            'reports.view',
            'reports.export',

            // Settings
            'settings.view',
            'settings.update',
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - all permissions (legacy)
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Superadmin - all permissions (new standard name)
        $superadmin = Role::create(['name' => 'Superadmin']);
        $superadmin->givePermissionTo(Permission::all());

        // Project Manager
        $projectManager = Role::create(['name' => 'project-manager']);
        $projectManager->givePermissionTo([
            'projects.view',
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
            'mr.approve',
            'pr.view',
            'pr.approve',
            'po.view',
            'gr.view',
            'usage.view',
            'progress.view',
            'progress.update',
            'dashboard.view',
            'reports.view',
            'reports.export',
        ]);

        // Site Manager
        $siteManager = Role::create(['name' => 'site-manager']);
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
        $logistics = Role::create(['name' => 'logistics']);
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
            'dashboard.view',
        ]);

        // Purchasing
        $purchasing = Role::create(['name' => 'purchasing']);
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
            'dashboard.view',
            'reports.view',
        ]);

        // Estimator (Quantity Surveyor)
        $estimator = Role::create(['name' => 'estimator']);
        $estimator->givePermissionTo([
            'projects.view',
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
        ]);

        // Engineer
        $engineer = Role::create(['name' => 'engineer']);
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
        $viewer = Role::create(['name' => 'viewer']);
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
        $administrator = Role::create(['name' => 'administrator']);
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
        $architect = Role::create(['name' => 'architect']);
        $architect->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Designer
        $designer = Role::create(['name' => 'designer']);
        $designer->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Project Admin
        $projectAdmin = Role::create(['name' => 'project-admin']);
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
        $supervisor = Role::create(['name' => 'supervisor']);
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
        $quantitySurveyor = Role::create(['name' => 'quantity-surveyor']);
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
        $drafter = Role::create(['name' => 'drafter']);
        $drafter->givePermissionTo([
            'projects.view',
            'rab.view',
            'schedule.view',
            'materials.view',
            'progress.view',
            'dashboard.view',
        ]);

        // Superintendent
        $superintendent = Role::create(['name' => 'superintendent']);
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
        $tukang = Role::create(['name' => 'tukang']);
        $tukang->givePermissionTo([
            'projects.view',
            'materials.view',
            'usage.view',
            'dashboard.view',
        ]);

        // Operator
        $operator = Role::create(['name' => 'operator']);
        $operator->givePermissionTo([
            'projects.view',
            'materials.view',
            'usage.view',
            'dashboard.view',
        ]);

        // HSE (Health, Safety, Environment)
        $hse = Role::create(['name' => 'hse']);
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
        $surveyor = Role::create(['name' => 'surveyor']);
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
