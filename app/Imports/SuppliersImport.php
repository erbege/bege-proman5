<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SuppliersImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        if (!isset($row['code']) || !isset($row['name'])) {
            return null;
        }

        return Supplier::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'contact_person' => $row['contact_person'] ?? null,
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'address' => $row['address'] ?? null,
                'city' => $row['city'] ?? null,
                'notes' => $row['notes'] ?? null,
                'is_active' => true,
            ]
        );
    }
}
