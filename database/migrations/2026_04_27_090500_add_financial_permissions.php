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
            'financials.view',
            'financials.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assign permissions to existing roles
        $financialRoles = [
            'Superadmin',
            'super-admin',
            'project-manager',
            'estimator',
            'purchasing',
            'administrator',
            'project-admin',
            'quantity-surveyor'
        ];

        foreach ($financialRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
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
            'financials.view',
            'financials.manage',
        ])->delete();

        // Spatie's delete() will also remove the association in role_has_permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
