<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-0001',
                'name' => 'CV Sumber Bahan Bangunan',
                'contact_person' => 'Budi Santoso',
                'phone' => '08123456789',
                'email' => 'budi@sumberbahan.co.id',
                'address' => 'Jl. Raya Industri No. 45',
                'city' => 'Surabaya',
                'notes' => 'Supplier utama semen dan pasir',
            ],
            [
                'code' => 'SUP-0002',
                'name' => 'PT Besi Baja Mandiri',
                'contact_person' => 'Herman Wijaya',
                'phone' => '08234567890',
                'email' => 'herman@besibaja.com',
                'address' => 'Jl. Baja Raya No. 12',
                'city' => 'Gresik',
                'notes' => 'Distributor besi beton dan kawat',
            ],
            [
                'code' => 'SUP-0003',
                'name' => 'UD Jaya Makmur',
                'contact_person' => 'Sutrisno',
                'phone' => '08345678901',
                'email' => 'jayamakmur@gmail.com',
                'address' => 'Jl. Pasar Bangunan No. 78',
                'city' => 'Sidoarjo',
                'notes' => 'Toko bangunan lengkap',
            ],
            [
                'code' => 'SUP-0004',
                'name' => 'PT Keramik Indonesia',
                'contact_person' => 'Indra Gunawan',
                'phone' => '08456789012',
                'email' => 'sales@keramikindonesia.co.id',
                'address' => 'Jl. Industri Keramik No. 5',
                'city' => 'Jakarta',
                'notes' => 'Distributor keramik dan granit',
            ],
            [
                'code' => 'SUP-0005',
                'name' => 'CV Cat Warna Warni',
                'contact_person' => 'Dewi Lestari',
                'phone' => '08567890123',
                'email' => 'dewi@catwarnawarni.com',
                'address' => 'Jl. Cat No. 33',
                'city' => 'Malang',
                'notes' => 'Supplier cat interior dan eksterior',
            ],
            [
                'code' => 'SUP-0006',
                'name' => 'PT Pipa Jaya',
                'contact_person' => 'Rudi Hartono',
                'phone' => '08678901234',
                'email' => 'pipajaya@yahoo.com',
                'address' => 'Jl. Pipa Raya No. 99',
                'city' => 'Surabaya',
                'notes' => 'Distributor pipa PVC dan PPR',
            ],
            [
                'code' => 'SUP-0007',
                'name' => 'UD Kayu Jati',
                'contact_person' => 'Bambang Suryadi',
                'phone' => '08789012345',
                'email' => 'kayujati@gmail.com',
                'address' => 'Jl. Kayu No. 15',
                'city' => 'Blitar',
                'notes' => 'Supplier kayu dan triplek',
            ],
            [
                'code' => 'SUP-0008',
                'name' => 'PT Atap Metal Indonesia',
                'contact_person' => 'Ahmad Fauzi',
                'phone' => '08890123456',
                'email' => 'sales@atapmetal.co.id',
                'address' => 'Jl. Atap No. 88',
                'city' => 'Pasuruan',
                'notes' => 'Rangka baja ringan dan genteng metal',
            ],
            [
                'code' => 'SUP-0009',
                'name' => 'CV Bata Prima',
                'contact_person' => 'Joko Widodo',
                'phone' => '08901234567',
                'email' => 'bataprima@gmail.com',
                'address' => 'Jl. Bata No. 21',
                'city' => 'Mojokerto',
                'notes' => 'Supplier bata merah dan batako',
            ],
            [
                'code' => 'SUP-0010',
                'name' => 'PT Ready Mix Jawa Timur',
                'contact_person' => 'Steven Tanoto',
                'phone' => '08012345678',
                'email' => 'steven@readymixjatim.com',
                'address' => 'Jl. Industri Ready Mix No. 1',
                'city' => 'Surabaya',
                'notes' => 'Supplier beton readymix',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['code' => $supplier['code']],
                array_merge($supplier, ['is_active' => true])
            );
        }
    }
}
