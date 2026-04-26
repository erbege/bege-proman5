<?php

namespace Database\Seeders;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\ProjectSchedule;
use App\Models\ProgressReport;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ComprehensiveProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users
        $admin = User::where('email', 'admin@proman.test')->first();
        $pm = User::where('email', 'pm@proman.test')->first();
        $sm = User::where('email', 'site@proman.test')->first();
        $logistic = User::where('email', 'logistic@proman.test')->first();
        $purchasing = User::where('email', 'purchasing@proman.test')->first();

        if (!$admin || !$pm) {
            $this->command->error('Please run DemoDataSeeder first!');
            return;
        }

        // Create project - 20 weeks duration, currently at week 16 (78% progress target)
        $startDate = Carbon::now()->subWeeks(16);
        $endDate = $startDate->copy()->addWeeks(20);

        $project = Project::create([
            'code' => 'PRJ-DEMO-001',
            'name' => 'Pembangunan Gedung Kantor 3 Lantai',
            'description' => 'Proyek pembangunan gedung kantor 3 lantai dengan luas bangunan 1.500 m2',
            'client_name' => 'PT Maju Bersama',
            'type' => 'construction',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'contract_value' => 5500000000,
            'status' => 'active',
            'location' => 'Jl. Sudirman No. 123, Jakarta Selatan',
            'notes' => 'Proyek demo dengan progress 78%',
            'created_by' => $pm->id,
        ]);

        $this->command->info("Created project: {$project->name}");

        // ============================================
        // RAB SECTIONS & ITEMS
        // ============================================
        $rabData = [
            [
                'code' => 'A',
                'name' => 'PEKERJAAN PERSIAPAN',
                'items' => [
                    ['code' => '1', 'name' => 'Pembersihan Lahan', 'unit' => 'm2', 'volume' => 2000, 'unit_price' => 15000, 'start_week' => 1, 'end_week' => 2, 'progress' => 100],
                    ['code' => '2', 'name' => 'Pengukuran & Uitzet', 'unit' => 'ls', 'volume' => 1, 'unit_price' => 25000000, 'start_week' => 1, 'end_week' => 2, 'progress' => 100],
                    ['code' => '3', 'name' => 'Direksi Keet', 'unit' => 'm2', 'volume' => 50, 'unit_price' => 500000, 'start_week' => 1, 'end_week' => 3, 'progress' => 100],
                    ['code' => '4', 'name' => 'Pagar Sementara', 'unit' => 'm', 'volume' => 150, 'unit_price' => 150000, 'start_week' => 1, 'end_week' => 2, 'progress' => 100],
                ],
            ],
            [
                'code' => 'B',
                'name' => 'PEKERJAAN TANAH',
                'items' => [
                    ['code' => '1', 'name' => 'Galian Tanah Pondasi', 'unit' => 'm3', 'volume' => 450, 'unit_price' => 85000, 'start_week' => 2, 'end_week' => 4, 'progress' => 100],
                    ['code' => '2', 'name' => 'Urugan Pasir Bawah Pondasi', 'unit' => 'm3', 'volume' => 120, 'unit_price' => 350000, 'start_week' => 3, 'end_week' => 5, 'progress' => 100],
                    ['code' => '3', 'name' => 'Urugan Tanah Kembali', 'unit' => 'm3', 'volume' => 200, 'unit_price' => 75000, 'start_week' => 5, 'end_week' => 6, 'progress' => 100],
                ],
            ],
            [
                'code' => 'C',
                'name' => 'PEKERJAAN PONDASI',
                'items' => [
                    ['code' => '1', 'name' => 'Pondasi Batu Kali', 'unit' => 'm3', 'volume' => 180, 'unit_price' => 850000, 'start_week' => 4, 'end_week' => 7, 'progress' => 100],
                    ['code' => '2', 'name' => 'Sloof 20/30', 'unit' => 'm3', 'volume' => 45, 'unit_price' => 3500000, 'start_week' => 6, 'end_week' => 8, 'progress' => 100],
                    ['code' => '3', 'name' => 'Footplat', 'unit' => 'm3', 'volume' => 35, 'unit_price' => 4200000, 'start_week' => 5, 'end_week' => 7, 'progress' => 100],
                ],
            ],
            [
                'code' => 'D',
                'name' => 'PEKERJAAN STRUKTUR',
                'items' => [
                    ['code' => '1', 'name' => 'Kolom K1 (40x40)', 'unit' => 'm3', 'volume' => 65, 'unit_price' => 4500000, 'start_week' => 7, 'end_week' => 12, 'progress' => 100],
                    ['code' => '2', 'name' => 'Balok B1 (30x50)', 'unit' => 'm3', 'volume' => 85, 'unit_price' => 4200000, 'start_week' => 9, 'end_week' => 14, 'progress' => 100],
                    ['code' => '3', 'name' => 'Plat Lantai t=12cm', 'unit' => 'm2', 'volume' => 1200, 'unit_price' => 450000, 'start_week' => 10, 'end_week' => 15, 'progress' => 95],
                    ['code' => '4', 'name' => 'Tangga Beton', 'unit' => 'ls', 'volume' => 2, 'unit_price' => 35000000, 'start_week' => 12, 'end_week' => 14, 'progress' => 100],
                ],
            ],
            [
                'code' => 'E',
                'name' => 'PEKERJAAN DINDING',
                'items' => [
                    ['code' => '1', 'name' => 'Pasangan Bata Ringan', 'unit' => 'm2', 'volume' => 1800, 'unit_price' => 180000, 'start_week' => 12, 'end_week' => 17, 'progress' => 85],
                    ['code' => '2', 'name' => 'Plesteran Dinding', 'unit' => 'm2', 'volume' => 3600, 'unit_price' => 85000, 'start_week' => 14, 'end_week' => 18, 'progress' => 70],
                    ['code' => '3', 'name' => 'Acian', 'unit' => 'm2', 'volume' => 3600, 'unit_price' => 45000, 'start_week' => 15, 'end_week' => 19, 'progress' => 55],
                ],
            ],
            [
                'code' => 'F',
                'name' => 'PEKERJAAN ATAP',
                'items' => [
                    ['code' => '1', 'name' => 'Rangka Atap Baja Ringan', 'unit' => 'm2', 'volume' => 550, 'unit_price' => 250000, 'start_week' => 14, 'end_week' => 16, 'progress' => 100],
                    ['code' => '2', 'name' => 'Penutup Atap Genteng Metal', 'unit' => 'm2', 'volume' => 550, 'unit_price' => 180000, 'start_week' => 15, 'end_week' => 17, 'progress' => 80],
                    ['code' => '3', 'name' => 'Plafond Gypsum', 'unit' => 'm2', 'volume' => 1200, 'unit_price' => 120000, 'start_week' => 16, 'end_week' => 19, 'progress' => 45],
                ],
            ],
            [
                'code' => 'G',
                'name' => 'PEKERJAAN LANTAI',
                'items' => [
                    ['code' => '1', 'name' => 'Keramik Lantai 60x60', 'unit' => 'm2', 'volume' => 1350, 'unit_price' => 185000, 'start_week' => 16, 'end_week' => 19, 'progress' => 40],
                    ['code' => '2', 'name' => 'Keramik Dinding KM', 'unit' => 'm2', 'volume' => 250, 'unit_price' => 165000, 'start_week' => 17, 'end_week' => 19, 'progress' => 25],
                ],
            ],
            [
                'code' => 'H',
                'name' => 'PEKERJAAN FINISHING',
                'items' => [
                    ['code' => '1', 'name' => 'Cat Dinding Interior', 'unit' => 'm2', 'volume' => 3200, 'unit_price' => 45000, 'start_week' => 18, 'end_week' => 20, 'progress' => 10],
                    ['code' => '2', 'name' => 'Cat Dinding Exterior', 'unit' => 'm2', 'volume' => 800, 'unit_price' => 55000, 'start_week' => 18, 'end_week' => 20, 'progress' => 5],
                    ['code' => '3', 'name' => 'Kusen & Daun Pintu', 'unit' => 'unit', 'volume' => 45, 'unit_price' => 2500000, 'start_week' => 17, 'end_week' => 20, 'progress' => 20],
                    ['code' => '4', 'name' => 'Kusen & Daun Jendela', 'unit' => 'unit', 'volume' => 60, 'unit_price' => 1800000, 'start_week' => 17, 'end_week' => 20, 'progress' => 15],
                ],
            ],
        ];

        $totalPrice = 0;
        $allItems = [];

        foreach ($rabData as $sectionData) {
            $section = RabSection::create([
                'project_id' => $project->id,
                'code' => $sectionData['code'],
                'name' => $sectionData['name'],
                'sort_order' => ord($sectionData['code']) - 64,
                'level' => 1,
            ]);

            foreach ($sectionData['items'] as $index => $itemData) {
                $itemTotalPrice = $itemData['volume'] * $itemData['unit_price'];
                $totalPrice += $itemTotalPrice;

                $item = RabItem::create([
                    'project_id' => $project->id,
                    'rab_section_id' => $section->id,
                    'code' => $itemData['code'],
                    'work_name' => $itemData['name'],
                    'volume' => $itemData['volume'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotalPrice,
                    'planned_start' => $startDate->copy()->addWeeks($itemData['start_week'] - 1),
                    'planned_end' => $startDate->copy()->addWeeks($itemData['end_week'] - 1)->endOfWeek(),
                    'actual_progress' => $itemData['progress'],
                    'sort_order' => $index + 1,
                ]);

                $allItems[] = ['item' => $item, 'progress' => $itemData['progress']];
            }
        }

        // Calculate weight percentages
        foreach ($allItems as $data) {
            $data['item']->update([
                'weight_percentage' => ($data['item']->total_price / $totalPrice) * 100,
            ]);
        }

        $this->command->info("Created " . count($allItems) . " RAB items");

        // ============================================
        // PROJECT SCHEDULES (Weekly S-Curve Data)
        // ============================================
        $plannedCumulative = 0;
        $actualCumulative = 0;

        for ($week = 1; $week <= 20; $week++) {
            // Calculate planned weight for this week
            $plannedWeight = 0;
            foreach ($allItems as $data) {
                $item = $data['item'];
                $itemStartWeek = $item->planned_start->diffInWeeks($startDate) + 1;
                $itemEndWeek = $item->planned_end->diffInWeeks($startDate) + 1;

                if ($week >= $itemStartWeek && $week <= $itemEndWeek) {
                    $itemDuration = $itemEndWeek - $itemStartWeek + 1;
                    $plannedWeight += $item->weight_percentage / $itemDuration;
                }
            }

            $plannedCumulative += $plannedWeight;

            // Calculate actual weight (for completed weeks up to week 16)
            $actualWeight = 0;
            if ($week <= 16) {
                foreach ($allItems as $data) {
                    $item = $data['item'];
                    $itemStartWeek = $item->planned_start->diffInWeeks($startDate) + 1;
                    $itemEndWeek = $item->planned_end->diffInWeeks($startDate) + 1;

                    if ($week >= $itemStartWeek && $week <= $itemEndWeek) {
                        $itemDuration = $itemEndWeek - $itemStartWeek + 1;
                        $weeklyProgress = $data['progress'] / $itemDuration;
                        $actualWeight += ($item->weight_percentage * $weeklyProgress / 100);
                    }
                }
                $actualCumulative += $actualWeight;
            }

            ProjectSchedule::create([
                'project_id' => $project->id,
                'week_number' => $week,
                'week_start' => $startDate->copy()->addWeeks($week - 1)->startOfWeek(),
                'week_end' => $startDate->copy()->addWeeks($week - 1)->endOfWeek(),
                'planned_weight' => $plannedWeight,
                'actual_weight' => $week <= 16 ? $actualWeight : 0,
                'planned_cumulative' => min($plannedCumulative, 100),
                'actual_cumulative' => min($actualCumulative, 100),
                'deviation' => $week <= 16 ? ($actualCumulative - $plannedCumulative) : 0,
            ]);
        }

        $this->command->info("Created 20 weekly schedules - Current progress: " . round($actualCumulative, 1) . "%");

        // ============================================
        // PROGRESS REPORTS
        // ============================================
        $reportDate = $startDate->copy();
        foreach ($allItems as $data) {
            $item = $data['item'];
            if ($data['progress'] > 0) {
                // Create 1-3 progress reports per item depending on progress
                $numReports = $data['progress'] >= 100 ? 2 : 1;
                $progressPerReport = $data['progress'] / $numReports;

                for ($i = 1; $i <= $numReports; $i++) {
                    $reportWeek = $item->planned_start->diffInWeeks($startDate) + ($i * 2);
                    ProgressReport::create([
                        'project_id' => $project->id,
                        'rab_item_id' => $item->id,
                        'report_date' => $startDate->copy()->addWeeks(min($reportWeek, 16))->addDays(rand(0, 6)),
                        'progress_percentage' => $progressPerReport,
                        'cumulative_progress' => min($i * $progressPerReport, $data['progress']),
                        'description' => "Laporan progress {$item->work_name}",
                        'weather' => ['sunny', 'cloudy', 'sunny', 'rainy'][rand(0, 3)],
                        'workers_count' => rand(5, 25),
                        'reported_by' => $sm->id ?? $pm->id,
                    ]);
                }
            }
        }

        $this->command->info("Created progress reports");

        // ============================================
        // INVENTORY, MATERIAL REQUESTS, PR, PO, GR
        // ============================================
        $materials = Material::all();
        $supplier = Supplier::first();

        // Create inventory for key materials
        $keyMaterials = $materials->take(10);
        foreach ($keyMaterials as $material) {
            Inventory::create([
                'project_id' => $project->id,
                'material_id' => $material->id,
                'quantity' => rand(50, 500),
                'reserved_qty' => rand(0, 20),
            ]);
        }

        $this->command->info("Created inventory for {$keyMaterials->count()} materials");

        // Create Material Requests (5 completed MRs)
        for ($i = 1; $i <= 5; $i++) {
            $mrDate = $startDate->copy()->addWeeks($i * 2);
            $mr = MaterialRequest::create([
                'project_id' => $project->id,
                'code' => "MR-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'request_date' => $mrDate,
                'status' => 'processed',
                'notes' => "Material request untuk minggu ke-" . ($i * 2 + 1),
                'requested_by' => $sm->id ?? $pm->id,
            ]);

            // Add 2-4 items per MR
            $mrMaterials = $keyMaterials->random(rand(2, 4));
            foreach ($mrMaterials as $material) {
                MaterialRequestItem::create([
                    'material_request_id' => $mr->id,
                    'material_id' => $material->id,
                    'quantity' => rand(20, 100),
                    'unit' => $material->unit,
                    'notes' => null,
                ]);
            }
        }

        $this->command->info("Created 5 Material Requests");

        // Create Purchase Requests (4 completed PRs)
        for ($i = 1; $i <= 4; $i++) {
            $prDate = $startDate->copy()->addWeeks($i * 2 + 1);
            $pr = PurchaseRequest::create([
                'project_id' => $project->id,
                'pr_number' => PurchaseRequest::generateNumber(),
                'request_date' => $prDate,
                'required_date' => $prDate->copy()->addDays(10),
                'status' => 'completed',
                'priority' => ['normal', 'high', 'normal', 'urgent'][$i - 1],
                'notes' => "Purchase request untuk minggu ke-" . ($i * 2 + 2),
                'requested_by' => $purchasing->id ?? $pm->id,
            ]);

            // Add 2-3 items per PR
            $prMaterials = $keyMaterials->random(rand(2, 3));
            foreach ($prMaterials as $material) {
                $qty = rand(50, 200);
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'material_id' => $material->id,
                    'quantity' => $qty,
                    'estimated_price' => $material->unit_price,
                    'notes' => null,
                ]);
            }
        }

        $this->command->info("Created 4 Purchase Requests");

        // Create Purchase Orders (3 received POs)
        for ($i = 1; $i <= 3; $i++) {
            $poDate = $startDate->copy()->addWeeks($i * 3);

            $poItems = [];
            $subtotal = 0;
            $poMaterials = $keyMaterials->random(rand(3, 5));

            foreach ($poMaterials as $material) {
                $qty = rand(50, 150);
                $price = $material->unit_price * (1 + (rand(-5, 10) / 100)); // ±5-10% price variance
                $total = $qty * $price;
                $subtotal += $total;

                $poItems[] = [
                    'material_id' => $material->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $total,
                ];
            }

            $taxAmount = $subtotal * 0.11;
            $totalAmount = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'project_id' => $project->id,
                'supplier_id' => $supplier->id,
                'po_number' => "PO-{$project->code}-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'order_date' => $poDate,
                'expected_delivery' => $poDate->copy()->addDays(7),
                'status' => 'received',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_terms' => 'Net 30',
                'notes' => "Purchase order batch {$i}",
                'created_by' => $purchasing->id ?? $pm->id,
            ]);

            foreach ($poItems as $itemData) {
                PurchaseOrderItem::create(array_merge($itemData, [
                    'purchase_order_id' => $po->id,
                    'received_qty' => $itemData['quantity'], // Fully received
                ]));
            }

            // Create Goods Receipt for each PO
            $gr = GoodsReceipt::create([
                'project_id' => $project->id,
                'purchase_order_id' => $po->id,
                'gr_number' => "GR-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'receipt_date' => $poDate->copy()->addDays(rand(5, 10)),
                'delivery_note_number' => 'DN-' . rand(1000, 9999),
                'notes' => 'Barang diterima dengan baik',
                'received_by' => $logistic->id ?? $pm->id,
            ]);

            // We need PO item IDs for GR items, query them
            $poItemModels = $po->items;
            foreach ($poItemModels as $poItem) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $poItem->id,
                    'material_id' => $poItem->material_id,
                    'quantity' => $poItem->quantity,
                ]);
            }
        }

        $this->command->info("Created 3 Purchase Orders with Goods Receipts");

        // Create 1 pending PR (ongoing)
        $pendingPrDate = Carbon::now()->subDays(5);
        $pendingPr = PurchaseRequest::create([
            'project_id' => $project->id,
            'pr_number' => PurchaseRequest::generateNumber(),
            'request_date' => $pendingPrDate,
            'required_date' => Carbon::now()->addDays(7),
            'status' => 'pending',
            'priority' => 'high',
            'notes' => 'Kebutuhan material untuk finishing',
            'requested_by' => $purchasing->id ?? $pm->id,
        ]);

        $finishingMaterials = $materials->whereIn('category', ['Cat', 'Keramik'])->take(3);
        foreach ($finishingMaterials as $material) {
            $qty = rand(30, 80);
            PurchaseRequestItem::create([
                'purchase_request_id' => $pendingPr->id,
                'material_id' => $material->id,
                'quantity' => $qty,
                'estimated_price' => $material->unit_price,
            ]);
        }

        $this->command->info("Created 1 pending Purchase Request");

        $this->command->info("✅ Comprehensive demo project created successfully!");
        $this->command->info("   Project: {$project->name}");
        $this->command->info("   Progress: ~78%");
        $this->command->info("   RAB Items: " . count($allItems));
        $this->command->info("   Schedule: 20 weeks (currently week 16)");
    }
}
