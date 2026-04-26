<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'code' => 'KLN-0001',
                'name' => 'PT Pembangunan Jaya Mandiri',
                'contact_person' => 'Ir. Suharto',
                'phone' => '08111222333',
                'email' => 'suharto@pjm.co.id',
                'address' => 'Jl. Sudirman No. 123, Gedung PJM Lt. 8',
                'city' => 'Jakarta',
                'notes' => 'Developer perumahan besar',
            ],
            [
                'code' => 'KLN-0002',
                'name' => 'Dinas PUPR Kota Surabaya',
                'contact_person' => 'Dr. Bambang Wahyudi',
                'phone' => '08222333444',
                'email' => 'pupr@surabaya.go.id',
                'address' => 'Jl. Pahlawan No. 1',
                'city' => 'Surabaya',
                'notes' => 'Instansi pemerintah',
            ],
            [
                'code' => 'KLN-0003',
                'name' => 'PT Graha Properti Indonesia',
                'contact_person' => 'Angela Tanoto',
                'phone' => '08333444555',
                'email' => 'angela@grahaprop.com',
                'address' => 'Jl. HR Muhammad No. 88',
                'city' => 'Surabaya',
                'notes' => 'Developer apartemen dan ruko',
            ],
            [
                'code' => 'KLN-0004',
                'name' => 'Yayasan Pendidikan Harapan Bangsa',
                'contact_person' => 'Dr. Maria Lestari',
                'phone' => '08444555666',
                'email' => 'maria@yhb.or.id',
                'address' => 'Jl. Pendidikan No. 45',
                'city' => 'Malang',
                'notes' => 'Pembangunan sekolah dan kampus',
            ],
            [
                'code' => 'KLN-0005',
                'name' => 'PT Mega Mall Indonesia',
                'contact_person' => 'William Hartono',
                'phone' => '08555666777',
                'email' => 'william@megamall.co.id',
                'address' => 'Jl. Raya Darmo No. 200',
                'city' => 'Surabaya',
                'notes' => 'Pengembang pusat perbelanjaan',
            ],
            [
                'code' => 'KLN-0006',
                'name' => 'RS Mitra Husada',
                'contact_person' => 'dr. Agus Salim, Sp.B',
                'phone' => '08666777888',
                'email' => 'agus@mitrahusada.co.id',
                'address' => 'Jl. Kesehatan No. 77',
                'city' => 'Sidoarjo',
                'notes' => 'Proyek renovasi dan ekspansi RS',
            ],
            [
                'code' => 'KLN-0007',
                'name' => 'PT Pabrik Gula Nusantara',
                'contact_person' => 'Ir. Hendro Wibowo',
                'phone' => '08777888999',
                'email' => 'hendro@pgn.co.id',
                'address' => 'Jl. Industri Gula No. 1',
                'city' => 'Kediri',
                'notes' => 'Proyek industrial',
            ],
            [
                'code' => 'KLN-0008',
                'name' => 'Hotel Grand Surya',
                'contact_person' => 'Christine Lee',
                'phone' => '08888999000',
                'email' => 'christine@grandsurya.com',
                'address' => 'Jl. Tunjungan No. 99',
                'city' => 'Surabaya',
                'notes' => 'Renovasi dan interior hotel',
            ],
            [
                'code' => 'KLN-0009',
                'name' => 'PT Logistik Nusantara',
                'contact_person' => 'Hendra Kusuma',
                'phone' => '08999000111',
                'email' => 'hendra@logistiknusa.co.id',
                'address' => 'Jl. Pelabuhan No. 5',
                'city' => 'Gresik',
                'notes' => 'Pembangunan gudang dan warehouse',
            ],
            [
                'code' => 'KLN-0010',
                'name' => 'Bpk. Andi Wijaya (Personal)',
                'contact_person' => 'Andi Wijaya',
                'phone' => '08100011122',
                'email' => 'andiwijaya@gmail.com',
                'address' => 'Jl. Rungkut Asri No. 15',
                'city' => 'Surabaya',
                'notes' => 'Proyek rumah tinggal pribadi',
            ],
        ];

        foreach ($clients as $client) {
            Client::updateOrCreate(
                ['code' => $client['code']],
                array_merge($client, ['is_active' => true])
            );
        }
    }
}
