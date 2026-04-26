<?php

namespace App\Exports;

use App\Exports\Sheets\RabRecapSheet;
use App\Exports\Sheets\RabDetailSheet;
use App\Exports\Sheets\RabAhspSheet;
use App\Models\Project;
use App\Models\RabSection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class RabExport implements WithMultipleSheets
{
    use Exportable;

    protected Project $project;
    protected $sections;
    protected $grandTotal;
    protected $ahspItems;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->loadData();
    }

    protected function loadData(): void
    {
        // Load root sections with children and items
        $this->sections = $this->project->rabSections()
            ->whereNull('parent_id')
            ->with(['children.items', 'children.children.items', 'items'])
            ->get()
            ->sortBy('code', SORT_NATURAL);

        // Calculate grand total
        $this->grandTotal = $this->project->rabItems()->sum('total_price');

        // Load AHSP items with their price snapshots
        $this->ahspItems = $this->project->rabItems()
            ->whereNotNull('ahsp_work_type_id')
            ->with(['ahspWorkType.components', 'priceSnapshot', 'section'])
            ->get()
            ->sortBy(function ($item) {
                return $item->section?->code . '.' . $item->code;
            }, SORT_NATURAL);
    }

    public function sheets(): array
    {
        // Get all RAB items for AHSP sheet (will show what data is available)
        $allItems = $this->project->rabItems()
            ->with(['ahspWorkType.components', 'priceSnapshot', 'section'])
            ->get()
            ->sortBy(function ($item) {
                return $item->section?->code . '.' . $item->code;
            }, SORT_NATURAL);

        return [
            new RabRecapSheet($this->project, $this->sections, $this->grandTotal),
            new RabDetailSheet($this->project, $this->sections, $this->grandTotal),
            new RabAhspSheet($this->project, $allItems),
        ];
    }
}
