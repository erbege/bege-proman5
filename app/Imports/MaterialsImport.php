<?php

namespace App\Imports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MaterialsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!isset($row['code']) || !isset($row['name'])) {
            return null;
        }

        return Material::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'unit' => $row['unit'] ?? 'pcs',
                'category' => $row['category'] ?? 'general',
                'price' => $row['price'] ?? 0,
            ]
        );
    }
}
