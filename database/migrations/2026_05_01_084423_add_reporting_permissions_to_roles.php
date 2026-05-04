<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            'monthly_report.view',
            'monthly_report.manage',
            'monthly_report.approve',
            'monthly_report.publish',
            'monthly_report.comment',
            'weekly_report.approve',
            'weekly_report.comment',
        ];

        // Create new permissions
        foreach ($newPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Superadmin gets everything automatically due to Gate::before or manual sync,
        // but we'll explicitly sync it to the role just in case.
        $superadmin = Role::where('name', 'Superadmin')->first();
        if ($superadmin) {
            $superadmin->givePermissionTo($newPermissions);
        }

        // Project Manager
        $pm = Role::where('name', 'project-manager')->first();
        if ($pm) {
            $pm->givePermissionTo([
                'monthly_report.view',
                'monthly_report.manage',
                'monthly_report.approve',
                'monthly_report.publish',
                'weekly_report.approve',
            ]);
        }

        // Estimator
        $estimator = Role::where('name', 'estimator')->first();
        if ($estimator) {
            $estimator->givePermissionTo([
                'monthly_report.view',
            ]);
        }

        // Owner
        $owner = Role::where('name', 'owner')->first();
        if ($owner) {
            $owner->givePermissionTo([
                'monthly_report.view',
                'monthly_report.comment',
                'weekly_report.comment',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            'monthly_report.view',
            'monthly_report.manage',
            'monthly_report.approve',
            'monthly_report.publish',
            'monthly_report.comment',
            'weekly_report.approve',
            'weekly_report.comment',
        ];

        Permission::whereIn('name', $newPermissions)->delete();
    }
};
