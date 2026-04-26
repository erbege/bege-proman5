<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Traits\GeneratesUniqueCode;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use GeneratesUniqueCode;

    public function index()
    {
        $clients = Client::orderBy('name')
            ->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:clients,code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate code if empty
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateUniqueCode(Client::class, 'KLN');
        }

        Client::create($validated);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Klien berhasil ditambahkan.');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Klien berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Klien berhasil dihapus.');
    }
}
