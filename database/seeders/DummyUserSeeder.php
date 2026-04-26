<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define users per role (2-5 users each)
        $usersPerRole = [
            'project-manager' => [
                ['name' => 'Budi Santoso', 'email' => 'budi.pm@example.com'],
                ['name' => 'Siti Rahayu', 'email' => 'siti.pm@example.com'],
                ['name' => 'Agus Wijaya', 'email' => 'agus.pm@example.com'],
            ],
            'site-manager' => [
                ['name' => 'Ahmad Hidayat', 'email' => 'ahmad.sm@example.com'],
                ['name' => 'Rudi Hermawan', 'email' => 'rudi.sm@example.com'],
                ['name' => 'Dedi Kurniawan', 'email' => 'dedi.sm@example.com'],
                ['name' => 'Hendra Gunawan', 'email' => 'hendra.sm@example.com'],
            ],
            'logistics' => [
                ['name' => 'Dewi Lestari', 'email' => 'dewi.log@example.com'],
                ['name' => 'Rina Wati', 'email' => 'rina.log@example.com'],
                ['name' => 'Bambang Susilo', 'email' => 'bambang.log@example.com'],
            ],
            'purchasing' => [
                ['name' => 'Eko Prasetyo', 'email' => 'eko.pur@example.com'],
                ['name' => 'Yuni Astuti', 'email' => 'yuni.pur@example.com'],
                ['name' => 'Wahyu Nugroho', 'email' => 'wahyu.pur@example.com'],
            ],
            'estimator' => [
                ['name' => 'Andi Firmansyah', 'email' => 'andi.est@example.com'],
                ['name' => 'Ratna Sari', 'email' => 'ratna.est@example.com'],
                ['name' => 'Fajar Setiawan', 'email' => 'fajar.est@example.com'],
                ['name' => 'Maya Putri', 'email' => 'maya.est@example.com'],
            ],
            'engineer' => [
                ['name' => 'Rizki Ramadhan', 'email' => 'rizki.eng@example.com'],
                ['name' => 'Indra Permana', 'email' => 'indra.eng@example.com'],
                ['name' => 'Dian Pratiwi', 'email' => 'dian.eng@example.com'],
                ['name' => 'Arif Budiman', 'email' => 'arif.eng@example.com'],
                ['name' => 'Nina Safitri', 'email' => 'nina.eng@example.com'],
            ],
            'architect' => [
                ['name' => 'Putra Mahendra', 'email' => 'putra.arch@example.com'],
                ['name' => 'Citra Dewi', 'email' => 'citra.arch@example.com'],
            ],
            'designer' => [
                ['name' => 'Galih Pratama', 'email' => 'galih.des@example.com'],
                ['name' => 'Anisa Rahma', 'email' => 'anisa.des@example.com'],
                ['name' => 'Bayu Aditya', 'email' => 'bayu.des@example.com'],
            ],
            'project-admin' => [
                ['name' => 'Putri Handayani', 'email' => 'putri.pa@example.com'],
                ['name' => 'Lina Marlina', 'email' => 'lina.pa@example.com'],
                ['name' => 'Tika Amelia', 'email' => 'tika.pa@example.com'],
            ],
            'supervisor' => [
                ['name' => 'Hadi Purnomo', 'email' => 'hadi.spv@example.com'],
                ['name' => 'Joko Widodo', 'email' => 'joko.spv@example.com'],
                ['name' => 'Surya Dharma', 'email' => 'surya.spv@example.com'],
                ['name' => 'Teguh Santosa', 'email' => 'teguh.spv@example.com'],
            ],
            'quantity-surveyor' => [
                ['name' => 'Faisal Ahmad', 'email' => 'faisal.qs@example.com'],
                ['name' => 'Irma Suryani', 'email' => 'irma.qs@example.com'],
                ['name' => 'Reza Maulana', 'email' => 'reza.qs@example.com'],
            ],
            'drafter' => [
                ['name' => 'Kevin Saputra', 'email' => 'kevin.drf@example.com'],
                ['name' => 'Mega Wulandari', 'email' => 'mega.drf@example.com'],
            ],
            'superintendent' => [
                ['name' => 'Umar Bakri', 'email' => 'umar.sup@example.com'],
                ['name' => 'Slamet Riyadi', 'email' => 'slamet.sup@example.com'],
                ['name' => 'Anton Subianto', 'email' => 'anton.sup@example.com'],
            ],
            'tukang' => [
                ['name' => 'Paijo Sukamto', 'email' => 'paijo.tkg@example.com'],
                ['name' => 'Karno Sumarno', 'email' => 'karno.tkg@example.com'],
                ['name' => 'Sukri Hartono', 'email' => 'sukri.tkg@example.com'],
                ['name' => 'Warno Suparman', 'email' => 'warno.tkg@example.com'],
                ['name' => 'Darto Sumedi', 'email' => 'darto.tkg@example.com'],
            ],
            'operator' => [
                ['name' => 'Bejo Santoso', 'email' => 'bejo.opr@example.com'],
                ['name' => 'Parjo Widodo', 'email' => 'parjo.opr@example.com'],
                ['name' => 'Marno Sudarmo', 'email' => 'marno.opr@example.com'],
            ],
            'hse' => [
                ['name' => 'Siska Permata', 'email' => 'siska.hse@example.com'],
                ['name' => 'Denny Setiawan', 'email' => 'denny.hse@example.com'],
            ],
            'surveyor' => [
                ['name' => 'Tommy Gunawan', 'email' => 'tommy.srv@example.com'],
                ['name' => 'Adrian Putra', 'email' => 'adrian.srv@example.com'],
                ['name' => 'Ferry Handoko', 'email' => 'ferry.srv@example.com'],
            ],
        ];

        $defaultPassword = Hash::make('password');

        foreach ($usersPerRole as $roleName => $users) {
            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                $this->command->warn("Role '{$roleName}' not found, skipping...");
                continue;
            }

            foreach ($users as $userData) {
                // Check if user already exists
                $existingUser = User::where('email', $userData['email'])->first();

                if ($existingUser) {
                    $this->command->info("User '{$userData['email']}' already exists, skipping...");
                    continue;
                }

                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($role);

                $this->command->info("Created user: {$userData['name']} ({$roleName})");
            }
        }

        $this->command->info('');
        $this->command->info('Dummy users created successfully!');
        $this->command->info('Default password for all users: password');
    }
}
