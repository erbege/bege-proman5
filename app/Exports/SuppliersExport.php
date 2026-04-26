<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Supplier::orderBy('name')->get();
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

    public function map($supplier): array
    {
        return [
            $supplier->code,
            $supplier->name,
            $supplier->contact_person,
            $supplier->phone,
            $supplier->email,
            $supplier->address,
            $supplier->city,
            $supplier->notes,
            $supplier->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }
}
