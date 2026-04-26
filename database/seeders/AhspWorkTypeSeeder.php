<?php

namespace Database\Seeders;

use App\Models\AhspCategory;
use App\Models\AhspComponent;
use App\Models\AhspWorkType;
use Illuminate\Database\Seeder;

/**
 * Seeder for AHSP Work Types (Analisa Harga Satuan Pekerjaan)
 * Based on SE Dirjen Bina Konstruksi Nomor 182/SE/Dk/2025
 * 
 * Contains work types with components (labor, material, equipment) and coefficients
 */
class AhspWorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $source = 'PUPR';
        $reference = 'SE Bina Konstruksi 182/SE/Dk/2025';

        // =====================================================
        // PEKERJAAN PERSIAPAN (Category 1)
        // =====================================================
        $this->createWorkType('1.1.1', '1.1', 'Pembersihan lapangan ringan dan perataan', 'm2', 0.035, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.005],
        ]);

        $this->createWorkType('1.1.2', '1.1', 'Pembuatan bouwplank / bowplank', 'm', 0.50, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.03', 'name' => 'Tukang Kayu', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.01],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.005],
            ['type' => 'material', 'code' => 'M.42', 'name' => 'Kayu Bekisting', 'unit' => 'm3', 'coefficient' => 0.012],
            ['type' => 'material', 'code' => 'M.46', 'name' => 'Paku 2" - 5"', 'unit' => 'kg', 'coefficient' => 0.05],
        ]);

        $this->createWorkType('1.2.1', '1.2', 'Pembongkaran bangunan lama', 'm2', 0.03, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.25],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.025],
        ]);

        // =====================================================
        // PEKERJAAN TANAH (Category 2)
        // =====================================================
        $this->createWorkType('2.1.1', '2.1', 'Galian tanah biasa sedalam 1 m', 'm3', 0.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.75],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.025],
        ]);

        $this->createWorkType('2.1.2', '2.1', 'Galian tanah biasa sedalam 2 m', 'm3', 0.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 1.00],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.033],
        ]);

        $this->createWorkType('2.2.1', '2.2', 'Urugan tanah kembali', 'm3', 0.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.25],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.008],
        ]);

        $this->createWorkType('2.2.2', '2.2', 'Urugan pasir bawah lantai t=10cm', 'm2', 0.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.03],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.001],
            ['type' => 'material', 'code' => 'M.12', 'name' => 'Pasir Urug', 'unit' => 'm3', 'coefficient' => 0.11],
        ]);

        $this->createWorkType('2.2.3', '2.2', 'Urugan sirtu dipadatkan', 'm3', 0.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.20],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.02],
            ['type' => 'material', 'code' => 'M.15', 'name' => 'Sirtu', 'unit' => 'm3', 'coefficient' => 1.20],
            ['type' => 'equipment', 'code' => 'E.03', 'name' => 'Stamper / Compactor', 'unit' => 'jam', 'coefficient' => 0.15],
        ]);

        // =====================================================
        // PEKERJAAN PONDASI (Category 3)
        // =====================================================
        $this->createWorkType('3.1.1', '3.1', 'Pasangan pondasi batu kali 1Pc : 4Ps', 'm3', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 1.50],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.75],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.075],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.075],
            ['type' => 'material', 'code' => 'M.14', 'name' => 'Batu Belah / Kali 15-20 cm', 'unit' => 'm3', 'coefficient' => 1.20],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 2.48],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.52],
        ]);

        $this->createWorkType('3.1.2', '3.1', 'Pasangan pondasi batu kali 1Pc : 5Ps', 'm3', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 1.50],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.75],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.075],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.075],
            ['type' => 'material', 'code' => 'M.14', 'name' => 'Batu Belah / Kali 15-20 cm', 'unit' => 'm3', 'coefficient' => 1.20],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 2.00],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.52],
        ]);

        // =====================================================
        // PEKERJAAN BETON (Category 4)
        // =====================================================
        $this->createWorkType('4.1.1', '4.1', 'Bekisting pondasi', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.52],
            ['type' => 'labor', 'code' => 'L.03', 'name' => 'Tukang Kayu', 'unit' => 'OH', 'coefficient' => 0.26],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.026],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.026],
            ['type' => 'material', 'code' => 'M.42', 'name' => 'Kayu Bekisting', 'unit' => 'm3', 'coefficient' => 0.04],
            ['type' => 'material', 'code' => 'M.46', 'name' => 'Paku 2" - 5"', 'unit' => 'kg', 'coefficient' => 0.40],
            ['type' => 'material', 'code' => 'M.36', 'name' => 'Kawat Bendrat', 'unit' => 'kg', 'coefficient' => 0.10],
        ]);

        $this->createWorkType('4.1.2', '4.1', 'Bekisting kolom', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.66],
            ['type' => 'labor', 'code' => 'L.03', 'name' => 'Tukang Kayu', 'unit' => 'OH', 'coefficient' => 0.33],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.033],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.033],
            ['type' => 'material', 'code' => 'M.42', 'name' => 'Kayu Bekisting', 'unit' => 'm3', 'coefficient' => 0.05],
            ['type' => 'material', 'code' => 'M.46', 'name' => 'Paku 2" - 5"', 'unit' => 'kg', 'coefficient' => 0.40],
            ['type' => 'material', 'code' => 'M.36', 'name' => 'Kawat Bendrat', 'unit' => 'kg', 'coefficient' => 0.10],
        ]);

        $this->createWorkType('4.1.3', '4.1', 'Bekisting balok', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.66],
            ['type' => 'labor', 'code' => 'L.03', 'name' => 'Tukang Kayu', 'unit' => 'OH', 'coefficient' => 0.33],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.033],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.033],
            ['type' => 'material', 'code' => 'M.42', 'name' => 'Kayu Bekisting', 'unit' => 'm3', 'coefficient' => 0.059],
            ['type' => 'material', 'code' => 'M.46', 'name' => 'Paku 2" - 5"', 'unit' => 'kg', 'coefficient' => 0.50],
            ['type' => 'material', 'code' => 'M.36', 'name' => 'Kawat Bendrat', 'unit' => 'kg', 'coefficient' => 0.10],
        ]);

        $this->createWorkType('4.1.4', '4.1', 'Bekisting plat lantai', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.66],
            ['type' => 'labor', 'code' => 'L.03', 'name' => 'Tukang Kayu', 'unit' => 'OH', 'coefficient' => 0.33],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.033],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.033],
            ['type' => 'material', 'code' => 'M.42', 'name' => 'Kayu Bekisting', 'unit' => 'm3', 'coefficient' => 0.040],
            ['type' => 'material', 'code' => 'M.46', 'name' => 'Paku 2" - 5"', 'unit' => 'kg', 'coefficient' => 0.40],
        ]);

        $this->createWorkType('4.2.1', '4.2', 'Pembesian 1 kg besi beton', 'kg', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.007],
            ['type' => 'labor', 'code' => 'L.04', 'name' => 'Tukang Besi', 'unit' => 'OH', 'coefficient' => 0.007],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.0007],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.0004],
            ['type' => 'material', 'code' => 'M.32', 'name' => 'Besi Beton Ulir D10', 'unit' => 'kg', 'coefficient' => 1.05],
            ['type' => 'material', 'code' => 'M.36', 'name' => 'Kawat Bendrat', 'unit' => 'kg', 'coefficient' => 0.015],
        ]);

        $this->createWorkType('4.3.1', '4.3', 'Beton mutu K-175 (site mix)', 'm3', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 1.65],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.275],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.028],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.083],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 5.70],
            ['type' => 'material', 'code' => 'M.11', 'name' => 'Pasir Beton / Cor', 'unit' => 'm3', 'coefficient' => 0.54],
            ['type' => 'material', 'code' => 'M.13', 'name' => 'Kerikil / Split', 'unit' => 'm3', 'coefficient' => 0.81],
            ['type' => 'material', 'code' => 'M.112', 'name' => 'Air Bersih', 'unit' => 'ltr', 'coefficient' => 215],
            ['type' => 'equipment', 'code' => 'E.01', 'name' => 'Molen / Concrete Mixer 0.35 m3', 'unit' => 'jam', 'coefficient' => 2.0],
        ]);

        $this->createWorkType('4.3.2', '4.3', 'Beton mutu K-225 (ready mix)', 'm3', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.35],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.035],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.004],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.018],
            ['type' => 'material', 'code' => 'M.04', 'name' => 'Beton Ready Mix K-225', 'unit' => 'm3', 'coefficient' => 1.02],
            ['type' => 'equipment', 'code' => 'E.02', 'name' => 'Concrete Vibrator', 'unit' => 'jam', 'coefficient' => 0.50],
        ]);

        $this->createWorkType('4.3.3', '4.3', 'Beton mutu K-250 (ready mix)', 'm3', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.35],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.035],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.004],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.018],
            ['type' => 'material', 'code' => 'M.05', 'name' => 'Beton Ready Mix K-250', 'unit' => 'm3', 'coefficient' => 1.02],
            ['type' => 'equipment', 'code' => 'E.02', 'name' => 'Concrete Vibrator', 'unit' => 'jam', 'coefficient' => 0.50],
        ]);

        // =====================================================
        // PEKERJAAN DINDING (Category 5)
        // =====================================================
        $this->createWorkType('5.1.1', '5.1', 'Pasangan bata merah 1/2 batu 1Pc : 2Ps', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.30],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.01],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'material', 'code' => 'M.20', 'name' => 'Bata Merah 5x10x20', 'unit' => 'bh', 'coefficient' => 70],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 0.66],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.043],
        ]);

        $this->createWorkType('5.1.2', '5.1', 'Pasangan bata merah 1/2 batu 1Pc : 4Ps', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.30],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.01],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'material', 'code' => 'M.20', 'name' => 'Bata Merah 5x10x20', 'unit' => 'bh', 'coefficient' => 70],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 0.33],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.043],
        ]);

        $this->createWorkType('5.3.1', '5.3', 'Pasangan bata ringan (hebel) tebal 10 cm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.2667],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.1333],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.0133],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.0133],
            ['type' => 'material', 'code' => 'M.22', 'name' => 'Bata Ringan (Hebel) 10x20x60', 'unit' => 'm3', 'coefficient' => 0.11],
            ['type' => 'material', 'code' => 'M.64', 'name' => 'Semen Keramik / Tile Adhesive', 'unit' => 'kg', 'coefficient' => 3.50],
        ]);

        // =====================================================
        // PEKERJAAN PLESTERAN (Category 6)
        // =====================================================
        $this->createWorkType('6.1.1', '6.1', 'Plesteran dinding 1Pc : 2Ps tebal 15mm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.30],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.15],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 0.267],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.018],
        ]);

        $this->createWorkType('6.1.2', '6.1', 'Plesteran dinding 1Pc : 4Ps tebal 15mm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.30],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.15],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 0.134],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.018],
        ]);

        $this->createWorkType('6.2.1', '6.2', 'Acian dinding', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.20],
            ['type' => 'labor', 'code' => 'L.02', 'name' => 'Tukang Batu', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.01],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.01],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 0.10],
        ]);

        // =====================================================
        // PEKERJAAN LANTAI (Category 7)
        // =====================================================
        $this->createWorkType('7.1.1', '7.1', 'Pemasangan keramik lantai 40x40 cm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.35],
            ['type' => 'labor', 'code' => 'L.16', 'name' => 'Tukang Keramik', 'unit' => 'OH', 'coefficient' => 0.175],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.0175],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.0175],
            ['type' => 'material', 'code' => 'M.60', 'name' => 'Keramik Lantai 40x40 KW1', 'unit' => 'm2', 'coefficient' => 1.05],
            ['type' => 'material', 'code' => 'M.01', 'name' => 'Semen Portland (PC) 50 kg', 'unit' => 'zak', 'coefficient' => 0.20],
            ['type' => 'material', 'code' => 'M.10', 'name' => 'Pasir Pasang', 'unit' => 'm3', 'coefficient' => 0.02],
            ['type' => 'material', 'code' => 'M.65', 'name' => 'Nat Keramik', 'unit' => 'kg', 'coefficient' => 0.50],
        ]);

        $this->createWorkType('7.1.2', '7.1', 'Pemasangan keramik lantai 60x60 cm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.30],
            ['type' => 'labor', 'code' => 'L.16', 'name' => 'Tukang Keramik', 'unit' => 'OH', 'coefficient' => 0.15],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'material', 'code' => 'M.61', 'name' => 'Keramik Lantai 60x60 KW1', 'unit' => 'm2', 'coefficient' => 1.05],
            ['type' => 'material', 'code' => 'M.64', 'name' => 'Semen Keramik / Tile Adhesive', 'unit' => 'kg', 'coefficient' => 5.0],
            ['type' => 'material', 'code' => 'M.65', 'name' => 'Nat Keramik', 'unit' => 'kg', 'coefficient' => 0.40],
        ]);

        $this->createWorkType('7.1.3', '7.1', 'Pemasangan keramik dinding 25x40 cm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.40],
            ['type' => 'labor', 'code' => 'L.16', 'name' => 'Tukang Keramik', 'unit' => 'OH', 'coefficient' => 0.20],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.02],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.02],
            ['type' => 'material', 'code' => 'M.62', 'name' => 'Keramik Dinding 25x40 KW1', 'unit' => 'm2', 'coefficient' => 1.08],
            ['type' => 'material', 'code' => 'M.64', 'name' => 'Semen Keramik / Tile Adhesive', 'unit' => 'kg', 'coefficient' => 5.0],
            ['type' => 'material', 'code' => 'M.65', 'name' => 'Nat Keramik', 'unit' => 'kg', 'coefficient' => 0.50],
        ]);

        // =====================================================
        // PEKERJAAN PLAFON (Category 8)
        // =====================================================
        $this->createWorkType('8.1.1', '8.1', 'Rangka plafon hollow 40x40', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.15],
            ['type' => 'labor', 'code' => 'L.17', 'name' => 'Tukang Plafon', 'unit' => 'OH', 'coefficient' => 0.15],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.0075],
            ['type' => 'material', 'code' => 'M.73', 'name' => 'Rangka Hollow 40x40', 'unit' => 'btg', 'coefficient' => 0.50],
        ]);

        $this->createWorkType('8.2.1', '8.2', 'Pemasangan plafon gypsum 9mm', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.17', 'name' => 'Tukang Plafon', 'unit' => 'OH', 'coefficient' => 0.10],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.01],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.005],
            ['type' => 'material', 'code' => 'M.70', 'name' => 'Gypsum Board 9mm', 'unit' => 'lbr', 'coefficient' => 0.36],
        ]);

        // =====================================================
        // PEKERJAAN ATAP (Category 9)
        // =====================================================
        $this->createWorkType('9.2.1', '9.2', 'Rangka atap baja ringan + genteng metal', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.08],
            ['type' => 'labor', 'code' => 'L.18', 'name' => 'Tukang Baja Ringan', 'unit' => 'OH', 'coefficient' => 0.08],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.008],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.004],
            ['type' => 'material', 'code' => 'M.84', 'name' => 'Baja Ringan C75.075', 'unit' => 'btg', 'coefficient' => 1.00],
            ['type' => 'material', 'code' => 'M.85', 'name' => 'Baja Ringan Reng', 'unit' => 'btg', 'coefficient' => 1.50],
            ['type' => 'material', 'code' => 'M.82', 'name' => 'Genteng Metal', 'unit' => 'm2', 'coefficient' => 1.05],
        ]);

        // =====================================================
        // PEKERJAAN CAT (Category 11)
        // =====================================================
        $this->createWorkType('11.1.1', '11.1', 'Pengecatan tembok baru (2x cat dasar + 2x cat akhir)', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.02],
            ['type' => 'labor', 'code' => 'L.05', 'name' => 'Tukang Cat', 'unit' => 'OH', 'coefficient' => 0.063],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.0063],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.003],
            ['type' => 'material', 'code' => 'M.54', 'name' => 'Plamir Tembok', 'unit' => 'kg', 'coefficient' => 0.10],
            ['type' => 'material', 'code' => 'M.51', 'name' => 'Cat Tembok Eksterior (5kg)', 'unit' => 'pail', 'coefficient' => 0.04],
        ]);

        $this->createWorkType('11.1.2', '11.1', 'Pengecatan tembok lama (1x plamir + 2x cat akhir)', 'm2', 10.00, $source, $reference, [
            ['type' => 'labor', 'code' => 'L.01', 'name' => 'Pekerja', 'unit' => 'OH', 'coefficient' => 0.015],
            ['type' => 'labor', 'code' => 'L.05', 'name' => 'Tukang Cat', 'unit' => 'OH', 'coefficient' => 0.05],
            ['type' => 'labor', 'code' => 'L.10', 'name' => 'Kepala Tukang', 'unit' => 'OH', 'coefficient' => 0.005],
            ['type' => 'labor', 'code' => 'L.11', 'name' => 'Mandor', 'unit' => 'OH', 'coefficient' => 0.002],
            ['type' => 'material', 'code' => 'M.54', 'name' => 'Plamir Tembok', 'unit' => 'kg', 'coefficient' => 0.08],
            ['type' => 'material', 'code' => 'M.50', 'name' => 'Cat Tembok Interior (5kg)', 'unit' => 'pail', 'coefficient' => 0.033],
        ]);

        $this->command->info("AHSP Work Types seeded successfully!");
        $this->command->info("Total Work Types: " . AhspWorkType::count());
        $this->command->info("Total Components: " . AhspComponent::count());
    }

    /**
     * Create a work type with its components
     */
    private function createWorkType(
        string $code,
        string $categoryCode,
        string $name,
        string $unit,
        float $overheadPercentage,
        string $source,
        string $reference,
        array $components
    ): void {
        $category = AhspCategory::where('code', $categoryCode)->first();

        $workType = AhspWorkType::create([
            'ahsp_category_id' => $category?->id,
            'code' => $code,
            'name' => $name,
            'unit' => $unit,
            'source' => $source,
            'reference' => $reference,
            'overhead_percentage' => $overheadPercentage,
            'is_active' => true,
        ]);

        foreach ($components as $idx => $comp) {
            AhspComponent::create([
                'ahsp_work_type_id' => $workType->id,
                'code' => $comp['code'],
                'name' => $comp['name'],
                'component_type' => $comp['type'],
                'unit' => $comp['unit'],
                'coefficient' => $comp['coefficient'],
                'sort_order' => $idx,
            ]);
        }
    }
}
