<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * R7: Add po.approve and gr.approve permissions for dedicated approval control.
     * R8: Remove financials.manage from estimator role (too broad for that role).
     * Assign new approval permissions to appropriate roles only (Superadmin, PM).
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create new dedicated approval permissions
        $poApprove = Permission::findOrCreate('po.approve');
        $grApprove = Permission::findOrCreate('gr.approve');

        // Assign to roles that should be able to approve
        $approverRoles = ['Superadmin', 'super-admin', 'project-manager'];
        foreach ($approverRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo(['po.approve', 'gr.approve', 'mr.approve', 'pr.approve']);
            }
        }

        // R8: Remove financials.manage from estimator
        // Estimator should only view financials, not approve documents
        $estimator = Role::where('name', 'estimator')->first();
        if ($estimator) {
            $estimator->revokePermissionTo('financials.manage');
        }

        // Also ensure administrator has the new approval permissions
        $administrator = Role::where('name', 'administrator')->first();
        if ($administrator) {
            $administrator->givePermissionTo(['po.approve', 'gr.approve', 'mr.approve', 'pr.approve']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Restore financials.manage to estimator
        $estimator = Role::where('name', 'estimator')->first();
        if ($estimator) {
            $estimator->givePermissionTo('financials.manage');
        }

        // Remove the new permissions from all roles first
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->revokePermissionTo(['po.approve', 'gr.approve']);
        }

        // Delete the permissions
        Permission::whereIn('name', ['po.approve', 'gr.approve'])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
