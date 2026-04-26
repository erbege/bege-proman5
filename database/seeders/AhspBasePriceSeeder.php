<?php

namespace Database\Seeders;

use App\Models\AhspBasePrice;
use Illuminate\Database\Seeder;

/**
 * Seeder for AHSP Base Prices (Harga Satuan Dasar)
 * Based on SE Dirjen Bina Konstruksi Nomor 182/SE/Dk/2025
 * 
 * Reference prices for DKI Jakarta region
 */
class AhspBasePriceSeeder extends Seeder
{
    public function run(): void
    {
        $regionCode = 'ID-JK';
        $regionName = 'DKI Jakarta';
        $effectiveDate = '2025-01-01';
        $source = 'SE Bina Konstruksi 182/SE/Dk/2025';

        // =====================================================
        // A. TENAGA KERJA (LABOR)
        // =====================================================
        $laborPrices = [
            ['code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'price' => 125000],
            ['code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'price' => 150000],
            ['code' => 'L.03', 'name' => 'Tukang Kayu', 'unit' => 'OH', 'price' => 150000],
            ['code' => 'L.04', 'name' => 'Tukang Besi', 'unit' => 'OH', 'price' => 150000],
            ['code' => 'L.05', 'name' => 'Tukang Cat', 'unit' => 'OH', 'price' => 145000],
            ['code' => 'L.06', 'name' => 'Tukang Listrik', 'unit' => 'OH', 'price' => 160000],
            ['code' => 'L.07', 'name' => 'Tukang Pipa/Sanitair', 'unit' => 'OH', 'price' => 155000],
            ['code' => 'L.08', 'name' => 'Tukang Las', 'unit' => 'OH', 'price' => 165000],
            ['code' => 'L.09', 'name' => 'Tukang Gali', 'unit' => 'OH', 'price' => 130000],
            ['code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'price' => 175000],
            ['code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'price' => 185000],
            ['code' => 'L.12', 'name' => 'Operator Alat Berat', 'unit' => 'OH', 'price' => 200000],
            ['code' => 'L.13', 'name' => 'Pembantu Operator', 'unit' => 'OH', 'price' => 140000],
            ['code' => 'L.14', 'name' => 'Sopir', 'unit' => 'OH', 'price' => 160000],
            ['code' => 'L.15', 'name' => 'Tukang Aluminium', 'unit' => 'OH', 'price' => 155000],
            ['code' => 'L.16', 'name' => 'Tukang Keramik', 'unit' => 'OH', 'price' => 155000],
            ['code' => 'L.17', 'name' => 'Tukang Plafon', 'unit' => 'OH', 'price' => 150000],
            ['code' => 'L.18', 'name' => 'Tukang Baja Ringan', 'unit' => 'OH', 'price' => 160000],
        ];

        // =====================================================
        // B. BAHAN (MATERIALS)
        // =====================================================
        $materialPrices = [
            // Semen dan Beton
            ['code' => 'M.01', 'name' => 'Site Portland (PC) 50 kg', 'unit' => 'zak', 'price' => 75000],
            ['code' => 'M.02', 'name' => 'Semen Portland (PC) 40 kg', 'unit' => 'zak', 'price' => 65000],
            ['code' => 'M.03', 'name' => 'Beton Ready Mix K-175', 'unit' => 'm3', 'price' => 950000],
            ['code' => 'M.04', 'name' => 'Beton Ready Mix K-225', 'unit' => 'm3', 'price' => 1050000],
            ['code' => 'M.05', 'name' => 'Beton Ready Mix K-250', 'unit' => 'm3', 'price' => 1100000],
            ['code' => 'M.06', 'name' => 'Beton Ready Mix K-300', 'unit' => 'm3', 'price' => 1200000],
            ['code' => 'M.07', 'name' => 'Beton Ready Mix K-350', 'unit' => 'm3', 'price' => 1300000],

            // Pasir dan Agregat
            ['code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'price' => 350000],
            ['code' => 'M.11', 'name' => 'Pasir Beton / Cor', 'unit' => 'm3', 'price' => 400000],
            ['code' => 'M.12', 'name' => 'Pasir Urug', 'unit' => 'm3', 'price' => 280000],
            ['code' => 'M.13', 'name' => 'Kerikil / Split', 'unit' => 'm3', 'price' => 450000],
            ['code' => 'M.14', 'name' => 'Batu Belah / Kali 15-20 cm', 'unit' => 'm3', 'price' => 300000],
            ['code' => 'M.15', 'name' => 'Sirtu', 'unit' => 'm3', 'price' => 220000],
            ['code' => 'M.16', 'name' => 'Tanah Urug', 'unit' => 'm3', 'price' => 150000],

            // Bata dan Batako
            ['code' => 'M.20', 'name' => 'Bata Merah 5x10x20', 'unit' => 'bh', 'price' => 1200],
            ['code' => 'M.21', 'name' => 'Batako 10x20x40', 'unit' => 'bh', 'price' => 5500],
            ['code' => 'M.22', 'name' => 'Bata Ringan (Hebel) 10x20x60', 'unit' => 'm3', 'price' => 850000],
            ['code' => 'M.23', 'name' => 'Roster/Loster 20x20', 'unit' => 'bh', 'price' => 15000],

            // Besi dan Baja
            ['code' => 'M.30', 'name' => 'Besi Beton Polos D8', 'unit' => 'kg', 'price' => 13500],
            ['code' => 'M.31', 'name' => 'Besi Beton Polos D10', 'unit' => 'kg', 'price' => 13500],
            ['code' => 'M.32', 'name' => 'Besi Beton Ulir D10', 'unit' => 'kg', 'price' => 14500],
            ['code' => 'M.33', 'name' => 'Besi Beton Ulir D13', 'unit' => 'kg', 'price' => 14500],
            ['code' => 'M.34', 'name' => 'Besi Beton Ulir D16', 'unit' => 'kg', 'price' => 14500],
            ['code' => 'M.35', 'name' => 'Besi Beton Ulir D19', 'unit' => 'kg', 'price' => 14500],
            ['code' => 'M.36', 'name' => 'Kawat Bendrat', 'unit' => 'kg', 'price' => 25000],
            ['code' => 'M.37', 'name' => 'Wiremesh M6', 'unit' => 'm2', 'price' => 45000],
            ['code' => 'M.38', 'name' => 'Wiremesh M8', 'unit' => 'm2', 'price' => 75000],

            // Kayu
            ['code' => 'M.40', 'name' => 'Kayu Kelas II (Meranti)', 'unit' => 'm3', 'price' => 6500000],
            ['code' => 'M.41', 'name' => 'Kayu Kelas III (Kamper)', 'unit' => 'm3', 'price' => 5500000],
            ['code' => 'M.42', 'name' => 'Kayu Bekisting', 'unit' => 'm3', 'price' => 3500000],
            ['code' => 'M.43', 'name' => 'Triplek 9mm', 'unit' => 'lbr', 'price' => 165000],
            ['code' => 'M.44', 'name' => 'Triplek 12mm', 'unit' => 'lbr', 'price' => 195000],
            ['code' => 'M.45', 'name' => 'Multiplek 18mm', 'unit' => 'lbr', 'price' => 320000],
            ['code' => 'M.46', 'name' => 'Paku 2" - 5"', 'unit' => 'kg', 'price' => 22000],

            // Cat
            ['code' => 'M.50', 'name' => 'Cat Tembok Interior (5kg)', 'unit' => 'pail', 'price' => 175000],
            ['code' => 'M.51', 'name' => 'Cat Tembok Eksterior (5kg)', 'unit' => 'pail', 'price' => 235000],
            ['code' => 'M.52', 'name' => 'Cat Minyak/Kayu', 'unit' => 'kg', 'price' => 85000],
            ['code' => 'M.53', 'name' => 'Cat Besi/Baja', 'unit' => 'kg', 'price' => 95000],
            ['code' => 'M.54', 'name' => 'Plamir Tembok', 'unit' => 'kg', 'price' => 25000],
            ['code' => 'M.55', 'name' => 'Dempul Kayu', 'unit' => 'kg', 'price' => 35000],
            ['code' => 'M.56', 'name' => 'Meni Besi', 'unit' => 'kg', 'price' => 55000],

            // Keramik dan Granit
            ['code' => 'M.60', 'name' => 'Keramik Lantai 40x40 KW1', 'unit' => 'm2', 'price' => 75000],
            ['code' => 'M.61', 'name' => 'Keramik Lantai 60x60 KW1', 'unit' => 'm2', 'price' => 95000],
            ['code' => 'M.62', 'name' => 'Keramik Dinding 25x40 KW1', 'unit' => 'm2', 'price' => 85000],
            ['code' => 'M.63', 'name' => 'Granit 60x60 Lokal', 'unit' => 'm2', 'price' => 185000],
            ['code' => 'M.64', 'name' => 'Semen Keramik / Tile Adhesive', 'unit' => 'kg', 'price' => 15000],
            ['code' => 'M.65', 'name' => 'Nat Keramik', 'unit' => 'kg', 'price' => 12000],

            // Plafon
            ['code' => 'M.70', 'name' => 'Gypsum Board 9mm', 'unit' => 'lbr', 'price' => 85000],
            ['code' => 'M.71', 'name' => 'Kalsiboard 4mm', 'unit' => 'lbr', 'price' => 55000],
            ['code' => 'M.72', 'name' => 'Rangka Hollow 20x40', 'unit' => 'btg', 'price' => 65000],
            ['code' => 'M.73', 'name' => 'Rangka Hollow 40x40', 'unit' => 'btg', 'price' => 85000],
            ['code' => 'M.74', 'name' => 'List Gypsum', 'unit' => 'm', 'price' => 15000],

            // Atap
            ['code' => 'M.80', 'name' => 'Genteng Beton', 'unit' => 'bh', 'price' => 12000],
            ['code' => 'M.81', 'name' => 'Genteng Keramik', 'unit' => 'bh', 'price' => 15000],
            ['code' => 'M.82', 'name' => 'Genteng Metal', 'unit' => 'm2', 'price' => 125000],
            ['code' => 'M.83', 'name' => 'Atap Spandek 0.35mm', 'unit' => 'm2', 'price' => 85000],
            ['code' => 'M.84', 'name' => 'Baja Ringan C75.075', 'unit' => 'btg', 'price' => 95000],
            ['code' => 'M.85', 'name' => 'Baja Ringan Reng', 'unit' => 'btg', 'price' => 45000],
            ['code' => 'M.86', 'name' => 'Nok Genteng', 'unit' => 'bh', 'price' => 18000],

            // Pipa & Sanitair
            ['code' => 'M.90', 'name' => 'Pipa PVC AW 1/2"', 'unit' => 'btg', 'price' => 35000],
            ['code' => 'M.91', 'name' => 'Pipa PVC AW 3/4"', 'unit' => 'btg', 'price' => 45000],
            ['code' => 'M.92', 'name' => 'Pipa PVC D 2"', 'unit' => 'btg', 'price' => 65000],
            ['code' => 'M.93', 'name' => 'Pipa PVC D 3"', 'unit' => 'btg', 'price' => 95000],
            ['code' => 'M.94', 'name' => 'Pipa PVC D 4"', 'unit' => 'btg', 'price' => 125000],
            ['code' => 'M.95', 'name' => 'Closet Duduk Standar', 'unit' => 'set', 'price' => 1500000],
            ['code' => 'M.96', 'name' => 'Closet Jongkok', 'unit' => 'set', 'price' => 350000],
            ['code' => 'M.97', 'name' => 'Wastafel + Meja', 'unit' => 'set', 'price' => 850000],
            ['code' => 'M.98', 'name' => 'Floor Drain Stainless', 'unit' => 'bh', 'price' => 75000],
            ['code' => 'M.99', 'name' => 'Kran Air Standar', 'unit' => 'bh', 'price' => 85000],

            // Listrik
            ['code' => 'M.100', 'name' => 'Kabel NYM 2x2.5mm', 'unit' => 'm', 'price' => 12000],
            ['code' => 'M.101', 'name' => 'Kabel NYM 3x2.5mm', 'unit' => 'm', 'price' => 18000],
            ['code' => 'M.102', 'name' => 'Kabel NYY 2x4mm', 'unit' => 'm', 'price' => 35000],
            ['code' => 'M.103', 'name' => 'Pipa Conduit PVC 20mm', 'unit' => 'btg', 'price' => 25000],
            ['code' => 'M.104', 'name' => 'Saklar Tunggal', 'unit' => 'bh', 'price' => 35000],
            ['code' => 'M.105', 'name' => 'Saklar Ganda', 'unit' => 'bh', 'price' => 45000],
            ['code' => 'M.106', 'name' => 'Stop Kontak', 'unit' => 'bh', 'price' => 40000],
            ['code' => 'M.107', 'name' => 'Fitting Lampu', 'unit' => 'bh', 'price' => 25000],
            ['code' => 'M.108', 'name' => 'MCB 1 Phase', 'unit' => 'bh', 'price' => 65000],

            // Lain-lain
            ['code' => 'M.110', 'name' => 'Waterproofing Membrane', 'unit' => 'm2', 'price' => 85000],
            ['code' => 'M.111', 'name' => 'Waterproofing Coating', 'unit' => 'kg', 'price' => 75000],
            ['code' => 'M.112', 'name' => 'Air Bersih', 'unit' => 'ltr', 'price' => 50],
            ['code' => 'M.113', 'name' => 'Lem Kayu', 'unit' => 'kg', 'price' => 55000],
            ['code' => 'M.114', 'name' => 'Lem PVC', 'unit' => 'kg', 'price' => 85000],
        ];

        // =====================================================
        // C. PERALATAN (EQUIPMENT)
        // =====================================================
        $equipmentPrices = [
            ['code' => 'E.01', 'name' => 'Molen / Concrete Mixer 0.35 m3', 'unit' => 'jam', 'price' => 45000],
            ['code' => 'E.02', 'name' => 'Concrete Vibrator', 'unit' => 'jam', 'price' => 35000],
            ['code' => 'E.03', 'name' => 'Stamper / Compactor', 'unit' => 'jam', 'price' => 65000],
            ['code' => 'E.04', 'name' => 'Excavator PC 200', 'unit' => 'jam', 'price' => 450000],
            ['code' => 'E.05', 'name' => 'Excavator Mini', 'unit' => 'jam', 'price' => 275000],
            ['code' => 'E.06', 'name' => 'Dump Truck 5 ton', 'unit' => 'rit', 'price' => 350000],
            ['code' => 'E.07', 'name' => 'Dump Truck 10 ton', 'unit' => 'rit', 'price' => 550000],
            ['code' => 'E.08', 'name' => 'Crane 25 ton', 'unit' => 'jam', 'price' => 850000],
            ['code' => 'E.09', 'name' => 'Genset 10 KVA', 'unit' => 'jam', 'price' => 85000],
            ['code' => 'E.10', 'name' => 'Pompa Air 2"', 'unit' => 'jam', 'price' => 25000],
            ['code' => 'E.11', 'name' => 'Scaffolding', 'unit' => 'set/bln', 'price' => 75000],
            ['code' => 'E.12', 'name' => 'Theodolit', 'unit' => 'hari', 'price' => 150000],
            ['code' => 'E.13', 'name' => 'Waterpass / Automatic Level', 'unit' => 'hari', 'price' => 85000],
            ['code' => 'E.14', 'name' => 'Bar Cutter & Bender', 'unit' => 'jam', 'price' => 35000],
            ['code' => 'E.15', 'name' => 'Mesin Las Listrik', 'unit' => 'jam', 'price' => 45000],
            ['code' => 'E.16', 'name' => 'Concrete Pump', 'unit' => 'm3', 'price' => 65000],
            ['code' => 'E.17', 'name' => 'Pile Driver / Hammer', 'unit' => 'jam', 'price' => 750000],
            ['code' => 'E.18', 'name' => 'Bulldozer D6', 'unit' => 'jam', 'price' => 650000],
            ['code' => 'E.19', 'name' => 'Wheel Loader', 'unit' => 'jam', 'price' => 450000],
            ['code' => 'E.20', 'name' => 'Roller / Vibro Roller', 'unit' => 'jam', 'price' => 350000],
        ];

        // Insert Labor Prices
        foreach ($laborPrices as $item) {
            AhspBasePrice::create([
                'code' => $item['code'],
                'name' => $item['name'],
                'component_type' => 'labor',
                'unit' => $item['unit'],
                'region_code' => $regionCode,
                'region_name' => $regionName,
                'price' => $item['price'],
                'effective_date' => $effectiveDate,
                'source' => $source,
                'is_active' => true,
            ]);
        }

        // Insert Material Prices
        foreach ($materialPrices as $item) {
            AhspBasePrice::create([
                'code' => $item['code'],
                'name' => $item['name'],
                'component_type' => 'material',
                'unit' => $item['unit'],
                'region_code' => $regionCode,
                'region_name' => $regionName,
                'price' => $item['price'],
                'effective_date' => $effectiveDate,
                'source' => $source,
                'is_active' => true,
            ]);
        }

        // Insert Equipment Prices
        foreach ($equipmentPrices as $item) {
            AhspBasePrice::create([
                'code' => $item['code'],
                'name' => $item['name'],
                'component_type' => 'equipment',
                'unit' => $item['unit'],
                'region_code' => $regionCode,
                'region_name' => $regionName,
                'price' => $item['price'],
                'effective_date' => $effectiveDate,
                'source' => $source,
                'is_active' => true,
            ]);
        }

        $totalPrices = count($laborPrices) + count($materialPrices) + count($equipmentPrices);
        $this->command->info("AHSP Base Prices seeded successfully!");
        $this->command->info("- Labor: " . count($laborPrices) . " items");
        $this->command->info("- Materials: " . count($materialPrices) . " items");
        $this->command->info("- Equipment: " . count($equipmentPrices) . " items");
        $this->command->info("- Total: {$totalPrices} items for region {$regionName}");
    }
}
