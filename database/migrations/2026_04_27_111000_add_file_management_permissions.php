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

        // Create new permissions
        $permissions = [
            'files.view',
            'files.create',
            'files.update',
            'files.delete',
            'files.manage-status',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // 1. Full Access (Admin & PM)
        $fullAccessRoles = [
            'Superadmin',
            'super-admin',
            'administrator',
            'project-manager',
        ];

        foreach ($fullAccessRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // 2. Contributor Access (Upload/Edit, no delete, no status management)
        $contributorRoles = [
            'site-manager',
            'estimator',
            'engineer',
            'architect',
            'designer',
            'project-admin',
            'quantity-surveyor',
            'superintendent',
            'surveyor',
        ];

        foreach ($contributorRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo([
                    'files.view',
                    'files.create',
                    'files.update',
                ]);
            }
        }

        // 3. View Only Access
        $viewOnlyRoles = [
            'supervisor',
            'logistics',
            'purchasing',
            'viewer',
            'drafter',
            'tukang',
            'operator',
            'hse',
        ];

        foreach ($viewOnlyRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo(['files.view']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions
        Permission::whereIn('name', [
            'files.view',
            'files.create',
            'files.update',
            'files.delete',
            'files.manage-status',
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
