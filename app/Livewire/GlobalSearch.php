<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Client;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\RabItem;
use Livewire\Component;
use Livewire\Attributes\On;

class GlobalSearch extends Component
{
    public string $query = '';
    public bool $showModal = false;
    public array $results = [];
    public int $selectedIndex = -1;

    protected $queryString = [];

    #[On('open-search-modal')]
    public function openModal()
    {
        $this->showModal = true;
        $this->query = '';
        $this->results = [];
        $this->selectedIndex = -1;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->query = '';
        $this->results = [];
        $this->selectedIndex = -1;
    }

    public function updatedQuery()
    {
        $this->selectedIndex = -1;

        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->search();
    }

    public function moveSelection(string $direction)
    {
        $count = count($this->results);
        if ($count === 0)
            return;

        if ($direction === 'down') {
            $this->selectedIndex = ($this->selectedIndex + 1) % $count;
        } else {
            $this->selectedIndex = ($this->selectedIndex - 1 + $count) % $count;
        }
    }

    public function selectCurrent()
    {
        if ($this->selectedIndex >= 0 && isset($this->results[$this->selectedIndex])) {
            $url = $this->results[$this->selectedIndex]['url'];
            $this->closeModal();
            $this->dispatch('navigate-to', url: $url);
        }
    }

    public function search()
    {
        $query = trim($this->query);

        if (strlen($query) < 2) {
            $this->results = [];
            return;
        }

        $user = auth()->user();
        $isPrivileged = $user->hasRole(['super-admin', 'Superadmin', 'administrator']) || $user->can('financials.manage');
        $results = [];

        // Search Projects
        $projectsQuery = Project::query();
        if (!$isPrivileged) {
            $projectsQuery->whereHas('team', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('is_active', true);
            });
        }
        $projects = $projectsQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($projects as $project) {
            $results[] = [
                'type' => 'project',
                'group' => 'Proyek',
                'icon' => 'folder',
                'color' => 'gold',
                'title' => $project->name,
                'subtitle' => $project->code . ' • ' . ($project->location ?? 'No location'),
                'url' => route('projects.show', $project),
            ];
        }

        // Search Purchase Orders
        $poQuery = PurchaseOrder::with(['project', 'supplier']);
        if (!$isPrivileged) {
            $poQuery->whereIn('project_id', function ($q) use ($user) {
                $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
            });
        }
        $purchaseOrders = $poQuery->where(function($q) use ($query) {
                $q->where('po_number', 'like', "%{$query}%")
                  ->orWhereHas('supplier', function ($sq) use ($query) {
                      $sq->where('name', 'like', "%{$query}%");
                  });
            })
            ->limit(5)
            ->get();

        foreach ($purchaseOrders as $po) {
            $results[] = [
                'type' => 'purchase_order',
                'group' => 'Purchase Order',
                'icon' => 'document-text',
                'color' => 'orange',
                'title' => $po->po_number,
                'subtitle' => ($po->supplier->name ?? 'No supplier') . ' • ' . ($po->project->name ?? 'No project'),
                'url' => route('projects.po.show', [$po->project_id, $po->id]),
            ];
        }

        // Search Purchase Requests
        $prQuery = PurchaseRequest::with(['project']);
        if (!$isPrivileged) {
            $prQuery->whereIn('project_id', function ($q) use ($user) {
                $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
            });
        }
        $purchaseRequests = $prQuery->where(function($q) use ($query) {
                $q->where('pr_number', 'like', "%{$query}%")
                  ->orWhere('status', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($purchaseRequests as $pr) {
            $results[] = [
                'type' => 'purchase_request',
                'group' => 'Purchase Request',
                'icon' => 'document-check',
                'color' => 'indigo',
                'title' => $pr->pr_number,
                'subtitle' => ($pr->project->name ?? 'No project') . ' • ' . $pr->status_label,
                'url' => route('projects.pr.show', [$pr->project_id, $pr->id]),
            ];
        }

        // Search RAB Items
        $rabQuery = RabItem::with(['project', 'section']);
        if (!$isPrivileged) {
            $rabQuery->whereIn('project_id', function ($q) use ($user) {
                $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
            });
        }
        $rabItems = $rabQuery->where(function($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                  ->orWhere('work_name', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($rabItems as $item) {
            $results[] = [
                'type' => 'rab_item',
                'group' => 'Item RAB',
                'icon' => 'document-currency-dollar',
                'color' => 'teal',
                'title' => $item->description,
                'subtitle' => ($item->project->name ?? 'No project') . ' • ' . ($item->section->name ?? ''),
                'url' => route('projects.rab.index', $item->project_id),
            ];
        }

        // Search Materials
        if ($user->can('materials.view')) {
            $materials = Material::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->orWhere('category', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($materials as $material) {
                $results[] = [
                    'type' => 'material',
                    'group' => 'Material',
                    'icon' => 'cube',
                    'color' => 'blue',
                    'title' => $material->name,
                    'subtitle' => $material->code . ' • ' . ($material->category ?? 'No category'),
                    'url' => route('materials.index', ['search' => $material->code]),
                ];
            }
        }

        // Search Suppliers
        if ($user->can('suppliers.view')) {
            $suppliers = Supplier::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->orWhere('city', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($suppliers as $supplier) {
                $results[] = [
                    'type' => 'supplier',
                    'group' => 'Supplier',
                    'icon' => 'truck',
                    'color' => 'green',
                    'title' => $supplier->name,
                    'subtitle' => $supplier->code . ' • ' . ($supplier->city ?? 'No city'),
                    'url' => route('suppliers.index', ['search' => $supplier->code]),
                ];
            }
        }

        // Search Clients
        if ($user->can('clients.view')) {
            $clients = Client::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->orWhere('contact_person', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($clients as $client) {
                $results[] = [
                    'type' => 'client',
                    'group' => 'Klien',
                    'icon' => 'user-group',
                    'color' => 'purple',
                    'title' => $client->name,
                    'subtitle' => $client->code . ' • ' . ($client->city ?? 'No city'),
                    'url' => route('clients.index', ['search' => $client->code]),
                ];
            }
        }

        $this->results = $results;
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
