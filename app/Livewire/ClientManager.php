<?php

namespace App\Livewire;

use App\Models\Client;
use App\Traits\GeneratesUniqueCode;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ClientsImport;
use App\Exports\ClientsExport;

class ClientManager extends Component
{
    use WithPagination, WithFileUploads, GeneratesUniqueCode;

    // Search & Filter
    public string $search = '';
    public string $statusFilter = '';
    public bool $showTrashed = false;

    // Modal states
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showImportModal = false;

    // Form data
    public ?int $editingId = null;
    public string $code = '';
    public string $name = '';
    public string $contactPerson = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $notes = '';
    public bool $isActive = true;

    // Import
    public $importFile;

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'showTrashed' => ['except' => false],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingShowTrashed()
    {
        $this->resetPage();
    }

    // Modal CRUD
    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->reset(['code', 'name', 'contactPerson', 'phone', 'email', 'address', 'city', 'notes', 'isActive', 'editingId']);

        if ($id) {
            $client = Client::withTrashed()->find($id);
            $this->editingId = $id;
            $this->code = $client->code ?? '';
            $this->name = $client->name;
            $this->contactPerson = $client->contact_person ?? '';
            $this->phone = $client->phone ?? '';
            $this->email = $client->email ?? '';
            $this->address = $client->address ?? '';
            $this->city = $client->city ?? '';
            $this->notes = $client->notes ?? '';
            $this->isActive = $client->is_active ?? true;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['code', 'name', 'contactPerson', 'phone', 'email', 'address', 'city', 'notes', 'isActive', 'editingId']);
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'contactPerson' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];

        if (!$this->editingId) {
            $rules['code'] = 'nullable|string|max:50|unique:clients,code';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'contact_person' => $this->contactPerson ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $client = Client::find($this->editingId);
            $data['is_active'] = $this->isActive;
            $client->update($data);
            session()->flash('success', 'Klien berhasil diperbarui.');
        } else {
            $data['code'] = $this->code ?: $this->generateUniqueCode(Client::class, 'KLN');
            $data['is_active'] = true;
            Client::create($data);
            session()->flash('success', 'Klien berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    // Delete
    public function confirmDelete(int $id, string $name)
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function delete()
    {
        Client::find($this->deleteId)->delete();
        session()->flash('success', 'Klien berhasil dihapus.');
        $this->closeDeleteModal();
    }

    public function restore(int $id)
    {
        Client::withTrashed()->find($id)->restore();
        session()->flash('success', 'Klien berhasil dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        Client::withTrashed()->find($id)->forceDelete();
        session()->flash('success', 'Klien dihapus permanen.');
    }

    // Import/Export
    public function openImportModal()
    {
        $this->reset(['importFile']);
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->reset(['importFile']);
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new ClientsImport, $this->importFile->getRealPath());
            session()->flash('success', 'Data klien berhasil diimpor.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }

        $this->closeImportModal();
    }

    public function export()
    {
        return Excel::download(new ClientsExport, 'clients-' . date('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $query = Client::query();

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('contact_person', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $clients = $query->orderBy('name')->paginate(15);

        return view('livewire.client-manager', [
            'clients' => $clients,
        ]);
    }
}
