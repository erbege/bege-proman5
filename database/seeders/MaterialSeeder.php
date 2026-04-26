<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            // Semen
            ['code' => 'MAT-0001', 'name' => 'Semen Portland 50kg', 'category' => 'Semen', 'unit' => 'zak', 'unit_price' => 65000],
            ['code' => 'MAT-0002', 'name' => 'Semen Mortar 40kg', 'category' => 'Semen', 'unit' => 'zak', 'unit_price' => 55000],

            // Pasir
            ['code' => 'MAT-0003', 'name' => 'Pasir Pasang', 'category' => 'Agregat', 'unit' => 'm3', 'unit_price' => 250000],
            ['code' => 'MAT-0004', 'name' => 'Pasir Beton', 'category' => 'Agregat', 'unit' => 'm3', 'unit_price' => 280000],
            ['code' => 'MAT-0005', 'name' => 'Pasir Urug', 'category' => 'Agregat', 'unit' => 'm3', 'unit_price' => 150000],

            // Batu
            ['code' => 'MAT-0006', 'name' => 'Batu Split 1/2', 'category' => 'Agregat', 'unit' => 'm3', 'unit_price' => 350000],
            ['code' => 'MAT-0007', 'name' => 'Batu Split 2/3', 'category' => 'Agregat', 'unit' => 'm3', 'unit_price' => 320000],
            ['code' => 'MAT-0008', 'name' => 'Batu Kali', 'category' => 'Agregat', 'unit' => 'm3', 'unit_price' => 180000],

            // Besi
            ['code' => 'MAT-0009', 'name' => 'Besi Beton D10 (12m)', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 85000],
            ['code' => 'MAT-0010', 'name' => 'Besi Beton D12 (12m)', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 120000],
            ['code' => 'MAT-0011', 'name' => 'Besi Beton D16 (12m)', 'category' => 'Besi', 'unit' => 'btg', 'unit_price' => 210000],
            ['code' => 'MAT-0012', 'name' => 'Kawat Bendrat', 'category' => 'Besi', 'unit' => 'kg', 'unit_price' => 18000],

            // Bata & Batako
            ['code' => 'MAT-0013', 'name' => 'Bata Merah Press', 'category' => 'Bata', 'unit' => 'bh', 'unit_price' => 800],
            ['code' => 'MAT-0014', 'name' => 'Batako 40x20x10', 'category' => 'Bata', 'unit' => 'bh', 'unit_price' => 3500],
            ['code' => 'MAT-0015', 'name' => 'Bata Ringan (Hebel)', 'category' => 'Bata', 'unit' => 'm3', 'unit_price' => 850000],

            // Keramik & Granit
            ['code' => 'MAT-0016', 'name' => 'Keramik Lantai 40x40 Putih', 'category' => 'Keramik', 'unit' => 'm2', 'unit_price' => 65000],
            ['code' => 'MAT-0017', 'name' => 'Keramik Lantai 60x60 Granit', 'category' => 'Keramik', 'unit' => 'm2', 'unit_price' => 120000],
            ['code' => 'MAT-0018', 'name' => 'Keramik Dinding 25x40 Putih', 'category' => 'Keramik', 'unit' => 'm2', 'unit_price' => 55000],

            // Cat
            ['code' => 'MAT-0019', 'name' => 'Cat Interior 5L', 'category' => 'Cat', 'unit' => 'pail', 'unit_price' => 185000],
            ['code' => 'MAT-0020', 'name' => 'Cat Eksterior 5L', 'category' => 'Cat', 'unit' => 'pail', 'unit_price' => 225000],
            ['code' => 'MAT-0021', 'name' => 'Cat Dasar Alkali 5L', 'category' => 'Cat', 'unit' => 'pail', 'unit_price' => 175000],

            // Kayu
            ['code' => 'MAT-0022', 'name' => 'Kayu Bekisting 4/6', 'category' => 'Kayu', 'unit' => 'btg', 'unit_price' => 35000],
            ['code' => 'MAT-0023', 'name' => 'Multiplek 12mm', 'category' => 'Kayu', 'unit' => 'lbr', 'unit_price' => 180000],
            ['code' => 'MAT-0024', 'name' => 'Paku 5cm', 'category' => 'Kayu', 'unit' => 'kg', 'unit_price' => 22000],

            // Pipa & Sanitair
            ['code' => 'MAT-0025', 'name' => 'Pipa PVC 4" (4m)', 'category' => 'Sanitair', 'unit' => 'btg', 'unit_price' => 95000],
            ['code' => 'MAT-0026', 'name' => 'Pipa PVC 3" (4m)', 'category' => 'Sanitair', 'unit' => 'btg', 'unit_price' => 65000],
            ['code' => 'MAT-0027', 'name' => 'Pipa PPR 3/4" (4m)', 'category' => 'Sanitair', 'unit' => 'btg', 'unit_price' => 45000],

            // Atap
            ['code' => 'MAT-0028', 'name' => 'Genteng Beton', 'category' => 'Atap', 'unit' => 'bh', 'unit_price' => 8500],
            ['code' => 'MAT-0029', 'name' => 'Seng Gelombang 180cm', 'category' => 'Atap', 'unit' => 'lbr', 'unit_price' => 85000],
            ['code' => 'MAT-0030', 'name' => 'Rangka Baja Ringan', 'category' => 'Atap', 'unit' => 'm2', 'unit_price' => 150000],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['code' => $material['code']],
                array_merge($material, ['is_active' => true, 'min_stock' => 10])
            );
        }
    }
}
