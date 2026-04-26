<?php

namespace Database\Seeders;

use App\Models\AhspCategory;
use Illuminate\Database\Seeder;

/**
 * Seeder for AHSP Categories based on SE Bina Konstruksi Nomor 182/SE/Dk/2025
 * Structure: AHSP Bidang Cipta Karya dan Perumahan
 */
class AhspCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // LEVEL 0 - Main Categories
            [
                'code' => '1',
                'name' => 'PEKERJAAN PERSIAPAN LAPANGAN / SITE WORK',
                'level' => 0,
                'children' => [
                    ['code' => '1.1', 'name' => 'Pekerjaan Persiapan', 'level' => 1],
                    ['code' => '1.2', 'name' => 'Pekerjaan Pembersihan', 'level' => 1],
                    ['code' => '1.3', 'name' => 'Pekerjaan Pengukuran dan Pemasangan Bowplank', 'level' => 1],
                ],
            ],
            [
                'code' => '2',
                'name' => 'PEKERJAAN TANAH',
                'level' => 0,
                'children' => [
                    ['code' => '2.1', 'name' => 'Pekerjaan Galian', 'level' => 1],
                    ['code' => '2.2', 'name' => 'Pekerjaan Urugan', 'level' => 1],
                    ['code' => '2.3', 'name' => 'Pekerjaan Pemadatan', 'level' => 1],
                ],
            ],
            [
                'code' => '3',
                'name' => 'PEKERJAAN PONDASI',
                'level' => 0,
                'children' => [
                    ['code' => '3.1', 'name' => 'Pondasi Batu Kali', 'level' => 1],
                    ['code' => '3.2', 'name' => 'Pondasi Beton Bertulang', 'level' => 1],
                    ['code' => '3.3', 'name' => 'Pondasi Tiang Pancang', 'level' => 1],
                    ['code' => '3.4', 'name' => 'Pondasi Sumuran', 'level' => 1],
                ],
            ],
            [
                'code' => '4',
                'name' => 'PEKERJAAN BETON',
                'level' => 0,
                'children' => [
                    ['code' => '4.1', 'name' => 'Pekerjaan Bekisting', 'level' => 1],
                    ['code' => '4.2', 'name' => 'Pekerjaan Pembesian', 'level' => 1],
                    ['code' => '4.3', 'name' => 'Pekerjaan Pengecoran', 'level' => 1],
                    ['code' => '4.4', 'name' => 'Beton Pracetak', 'level' => 1],
                ],
            ],
            [
                'code' => '5',
                'name' => 'PEKERJAAN DINDING',
                'level' => 0,
                'children' => [
                    ['code' => '5.1', 'name' => 'Pasangan Bata', 'level' => 1],
                    ['code' => '5.2', 'name' => 'Pasangan Batako', 'level' => 1],
                    ['code' => '5.3', 'name' => 'Pasangan Bata Ringan', 'level' => 1],
                    ['code' => '5.4', 'name' => 'Dinding Partisi', 'level' => 1],
                ],
            ],
            [
                'code' => '6',
                'name' => 'PEKERJAAN PLESTERAN DAN ACIAN',
                'level' => 0,
                'children' => [
                    ['code' => '6.1', 'name' => 'Plesteran Dinding', 'level' => 1],
                    ['code' => '6.2', 'name' => 'Acian Dinding', 'level' => 1],
                    ['code' => '6.3', 'name' => 'Benangan dan Tali Air', 'level' => 1],
                ],
            ],
            [
                'code' => '7',
                'name' => 'PEKERJAAN LANTAI',
                'level' => 0,
                'children' => [
                    ['code' => '7.1', 'name' => 'Lantai Keramik', 'level' => 1],
                    ['code' => '7.2', 'name' => 'Lantai Granit/Marmer', 'level' => 1],
                    ['code' => '7.3', 'name' => 'Lantai Vinyl/Parket', 'level' => 1],
                    ['code' => '7.4', 'name' => 'Lantai Beton', 'level' => 1],
                ],
            ],
            [
                'code' => '8',
                'name' => 'PEKERJAAN LANGIT-LANGIT / PLAFON',
                'level' => 0,
                'children' => [
                    ['code' => '8.1', 'name' => 'Rangka Plafon', 'level' => 1],
                    ['code' => '8.2', 'name' => 'Penutup Plafon', 'level' => 1],
                    ['code' => '8.3', 'name' => 'List Plafon', 'level' => 1],
                ],
            ],
            [
                'code' => '9',
                'name' => 'PEKERJAAN ATAP',
                'level' => 0,
                'children' => [
                    ['code' => '9.1', 'name' => 'Rangka Atap Kayu', 'level' => 1],
                    ['code' => '9.2', 'name' => 'Rangka Atap Baja Ringan', 'level' => 1],
                    ['code' => '9.3', 'name' => 'Penutup Atap', 'level' => 1],
                    ['code' => '9.4', 'name' => 'Talang dan Lisplang', 'level' => 1],
                ],
            ],
            [
                'code' => '10',
                'name' => 'PEKERJAAN PINTU DAN JENDELA',
                'level' => 0,
                'children' => [
                    ['code' => '10.1', 'name' => 'Kusen Kayu', 'level' => 1],
                    ['code' => '10.2', 'name' => 'Kusen Aluminium', 'level' => 1],
                    ['code' => '10.3', 'name' => 'Daun Pintu', 'level' => 1],
                    ['code' => '10.4', 'name' => 'Daun Jendela', 'level' => 1],
                    ['code' => '10.5', 'name' => 'Kaca dan Aksesoris', 'level' => 1],
                ],
            ],
            [
                'code' => '11',
                'name' => 'PEKERJAAN PENGECATAN',
                'level' => 0,
                'children' => [
                    ['code' => '11.1', 'name' => 'Cat Dinding', 'level' => 1],
                    ['code' => '11.2', 'name' => 'Cat Kayu', 'level' => 1],
                    ['code' => '11.3', 'name' => 'Cat Besi/Baja', 'level' => 1],
                ],
            ],
            [
                'code' => '12',
                'name' => 'PEKERJAAN SANITAIR',
                'level' => 0,
                'children' => [
                    ['code' => '12.1', 'name' => 'Pipa dan Fitting Air Bersih', 'level' => 1],
                    ['code' => '12.2', 'name' => 'Pipa dan Fitting Air Kotor', 'level' => 1],
                    ['code' => '12.3', 'name' => 'Peralatan Sanitair', 'level' => 1],
                    ['code' => '12.4', 'name' => 'Septictank dan Resapan', 'level' => 1],
                ],
            ],
            [
                'code' => '13',
                'name' => 'PEKERJAAN INSTALASI LISTRIK',
                'level' => 0,
                'children' => [
                    ['code' => '13.1', 'name' => 'Instalasi Penerangan', 'level' => 1],
                    ['code' => '13.2', 'name' => 'Instalasi Stop Kontak', 'level' => 1],
                    ['code' => '13.3', 'name' => 'Panel Listrik', 'level' => 1],
                    ['code' => '13.4', 'name' => 'Armatur dan Fitting', 'level' => 1],
                ],
            ],
            [
                'code' => '14',
                'name' => 'PEKERJAAN DRAINASE',
                'level' => 0,
                'children' => [
                    ['code' => '14.1', 'name' => 'Saluran Drainase', 'level' => 1],
                    ['code' => '14.2', 'name' => 'Bak Kontrol', 'level' => 1],
                    ['code' => '14.3', 'name' => 'Gorong-gorong', 'level' => 1],
                ],
            ],
            [
                'code' => '15',
                'name' => 'PEKERJAAN LAIN-LAIN',
                'level' => 0,
                'children' => [
                    ['code' => '15.1', 'name' => 'Pekerjaan Pembersihan Akhir', 'level' => 1],
                    ['code' => '15.2', 'name' => 'Pekerjaan Pemeliharaan', 'level' => 1],
                ],
            ],
        ];

        $sortOrder = 0;
        foreach ($categories as $category) {
            $parent = AhspCategory::create([
                'code' => $category['code'],
                'name' => $category['name'],
                'level' => $category['level'],
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ]);

            if (isset($category['children'])) {
                $childSortOrder = 0;
                foreach ($category['children'] as $child) {
                    AhspCategory::create([
                        'code' => $child['code'],
                        'name' => $child['name'],
                        'parent_id' => $parent->id,
                        'level' => $child['level'],
                        'sort_order' => $childSortOrder++,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('AHSP Categories seeded successfully! Total: ' . AhspCategory::count() . ' categories');
    }
}
