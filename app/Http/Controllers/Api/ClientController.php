<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * List all clients.
     * 
     * Get a paginated list of all clients.
     */
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get client details.
     * 
     * Get detailed information about a specific client.
     */
    public function show(Client $client)
    {
        return response()->json([
            'data' => $client
        ]);
    }
}
