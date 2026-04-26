<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
        $admin = User::firstOrCreate(
            ['email' => 'admin@proman.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');

        // Superadmin user (for new System Settings access)
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@proman.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superadmin->assignRole('Superadmin');

        $pm = User::firstOrCreate(
            ['email' => 'pm@proman.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $pm->assignRole('project-manager');

        $sm = User::firstOrCreate(
            ['email' => 'site@proman.test'],
            [
                'name' => 'Ahmad Wijaya',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $sm->assignRole('site-manager');

        $logistic = User::firstOrCreate(
            ['email' => 'logistic@proman.test'],
            [
                'name' => 'Dewi Sari',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $logistic->assignRole('logistics');

        $purchasing = User::firstOrCreate(
            ['email' => 'purchasing@proman.test'],
            [
                'name' => 'Eko Prasetyo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $purchasing->assignRole('purchasing');

        $estimator = User::firstOrCreate(
            ['email' => 'estimator@proman.test'],
            [
                'name' => 'Bambang Esti',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $estimator->assignRole('estimator');

        // Create demo suppliers
        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'PT Semen Indonesia', 'contact_person' => 'Rudi', 'phone' => '021-1234567', 'city' => 'Jakarta'],
            ['code' => 'SUP-002', 'name' => 'CV Pasir Jaya', 'contact_person' => 'Agus', 'phone' => '022-2345678', 'city' => 'Bandung'],
            ['code' => 'SUP-003', 'name' => 'UD Besi Makmur', 'contact_person' => 'Hendra', 'phone' => '031-3456789', 'city' => 'Surabaya'],
            ['code' => 'SUP-004', 'name' => 'PT Kayu Lestari', 'contact_person' => 'Wawan', 'phone' => '024-4567890', 'city' => 'Semarang'],
            ['code' => 'SUP-005', 'name' => 'CV Bata Merah', 'contact_person' => 'Yanto', 'phone' => '0274-567890', 'city' => 'Yogyakarta'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['code' => $supplier['code']], $supplier);
        }

        // Create demo materials
        $materials = [
            // Semen
            ['code' => 'MAT-001', 'name' => 'Semen PC 50 kg', 'category' => 'Semen', 'unit' => 'zak', 'unit_price' => 75000],
            ['code' => 'MAT-002', 'name' => 'Semen Putih 40 kg', 'category' => 'Semen', 'unit' => 'zak', 'unit_price' => 120000],

            // Pasir
            ['code' => 'MAT-003', 'name' => 'Pasir Pasang', 'category' => 'Pasir', 'unit' => 'm3', 'unit_price' => 350000],
            ['code' => 'MAT-004', 'name' => 'Pasir Cor', 'category' => 'Pasir', 'unit' => 'm3', 'unit_price' => 400000],
            ['code' => 'MAT-005', 'name' => 'Pasir Urug', 'category' => 'Pasir', 'unit' => 'm3', 'unit_price' => 250000],

            // Batu
            ['code' => 'MAT-006', 'name' => 'Batu Split 1-2', 'category' => 'Batu', 'unit' => 'm3', 'unit_price' => 450000],
            ['code' => 'MAT-007', 'name' => 'Batu Split 2-3', 'category' => 'Batu', 'unit' => 'm3', 'unit_price' => 420000],
            ['code' => 'MAT-008', 'name' => 'Batu Kali', 'category' => 'Batu', 'unit' => 'm3', 'unit_price' => 280000],

            // Besi
            ['code' => 'MAT-009', 'name' => 'Besi Beton D10', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 85000],
            ['code' => 'MAT-010', 'name' => 'Besi Beton D13', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 145000],
            ['code' => 'MAT-011', 'name' => 'Besi Beton D16', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 220000],
            ['code' => 'MAT-012', 'name' => 'Besi Beton D19', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 310000],
            ['code' => 'MAT-013', 'name' => 'Kawat Bendrat', 'category' => 'Besi', 'unit' => 'kg', 'unit_price' => 22000],

            // Bata
            ['code' => 'MAT-014', 'name' => 'Bata Merah', 'category' => 'Bata', 'unit' => 'bh', 'unit_price' => 1200],
            ['code' => 'MAT-015', 'name' => 'Bata Ringan 10cm', 'category' => 'Bata', 'unit' => 'bh', 'unit_price' => 12000],
            ['code' => 'MAT-016', 'name' => 'Batako', 'category' => 'Bata', 'unit' => 'bh', 'unit_price' => 3500],

            // Kayu
            ['code' => 'MAT-017', 'name' => 'Kayu Bekisting 5/7', 'category' => 'Kayu', 'unit' => 'btg', 'unit_price' => 45000],
            ['code' => 'MAT-018', 'name' => 'Triplek 9mm', 'category' => 'Kayu', 'unit' => 'lbr', 'unit_price' => 180000],
            ['code' => 'MAT-019', 'name' => 'Multiplek 18mm', 'category' => 'Kayu', 'unit' => 'lbr', 'unit_price' => 320000],

            // Atap
            ['code' => 'MAT-020', 'name' => 'Genteng Keramik', 'category' => 'Atap', 'unit' => 'bh', 'unit_price' => 12000],
            ['code' => 'MAT-021', 'name' => 'Genteng Metal', 'category' => 'Atap', 'unit' => 'm2', 'unit_price' => 95000],
            ['code' => 'MAT-022', 'name' => 'Baja Ringan C75', 'category' => 'Atap', 'unit' => 'btg', 'unit_price' => 85000],

            // Cat
            ['code' => 'MAT-023', 'name' => 'Cat Tembok Interior', 'category' => 'Cat', 'unit' => 'pail', 'unit_price' => 350000],
            ['code' => 'MAT-024', 'name' => 'Cat Tembok Exterior', 'category' => 'Cat', 'unit' => 'pail', 'unit_price' => 450000],
            ['code' => 'MAT-025', 'name' => 'Plamir Tembok', 'category' => 'Cat', 'unit' => 'kg', 'unit_price' => 25000],

            // Keramik
            ['code' => 'MAT-026', 'name' => 'Keramik Lantai 60x60', 'category' => 'Keramik', 'unit' => 'm2', 'unit_price' => 85000],
            ['code' => 'MAT-027', 'name' => 'Keramik Dinding 25x40', 'category' => 'Keramik', 'unit' => 'm2', 'unit_price' => 65000],
            ['code' => 'MAT-028', 'name' => 'Granit 60x60', 'category' => 'Keramik', 'unit' => 'm2', 'unit_price' => 180000],

            // Pipa
            ['code' => 'MAT-029', 'name' => 'Pipa PVC 4"', 'category' => 'Pipa', 'unit' => 'btg', 'unit_price' => 120000],
            ['code' => 'MAT-030', 'name' => 'Pipa PVC 3"', 'category' => 'Pipa', 'unit' => 'btg', 'unit_price' => 85000],
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate(['code' => $material['code']], $material);
        }
    }
}
