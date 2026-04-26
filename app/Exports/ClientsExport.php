<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Client::orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama',
            'Kontak Person',
            'Telepon',
            'Email',
            'Alamat',
            'Kota',
            'Catatan',
            'Status',
        ];
    }

    public function map($client): array
    {
        return [
            $client->code,
            $client->name,
            $client->contact_person,
            $client->phone,
            $client->email,
            $client->address,
            $client->city,
            $client->notes,
            $client->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }
}
