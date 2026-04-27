<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApprovalMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $matrices = [
            // Material Request (MR)
            ['document_type' => 'MR', 'level' => 1, 'role_name' => 'site_manager', 'min_amount' => 0],
            
            // Purchase Request (PR)
            ['document_type' => 'PR', 'level' => 1, 'role_name' => 'site_manager', 'min_amount' => 0],
            ['document_type' => 'PR', 'level' => 2, 'role_name' => 'project_manager', 'min_amount' => 0],
            
            // Purchase Order (PO)
            ['document_type' => 'PO', 'level' => 1, 'role_name' => 'procurement', 'min_amount' => 0],
            ['document_type' => 'PO', 'level' => 2, 'role_name' => 'project_manager', 'min_amount' => 0],
            ['document_type' => 'PO', 'level' => 3, 'role_name' => 'director', 'min_amount' => 100000000], // > 100 Juta
        ];

        foreach ($matrices as $matrix) {
            \App\Models\ApprovalMatrix::updateOrCreate(
                ['document_type' => $matrix['document_type'], 'level' => $matrix['level']],
                $matrix
            );
        }
    }
}
