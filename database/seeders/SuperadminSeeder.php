<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Superadmin role if not exists
        $role = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

        // Give all permissions to Superadmin
        $role->syncPermissions(Permission::all());

        // Create Superadmin user
        $user = User::firstOrCreate(
            ['email' => 'superadmin@proman.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign role
        if (!$user->hasRole('Superadmin')) {
            $user->assignRole('Superadmin');
        }

        $this->command->info('Superadmin user created successfully!');
        $this->command->info('Email: superadmin@proman.test');
        $this->command->info('Password: password');
    }
}
