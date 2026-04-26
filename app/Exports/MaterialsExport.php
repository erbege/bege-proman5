<?php

namespace App\Exports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Material::orderBy('category')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama',
            'Kategori',
            'Satuan',
            'Harga Satuan',
            'Stok Minimum',
            'Deskripsi',
            'Status',
        ];
    }

    public function map($material): array
    {
        return [
            $material->code,
            $material->name,
            $material->category,
            $material->unit,
            $material->unit_price,
            $material->min_stock,
            $material->description,
            $material->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }
}
