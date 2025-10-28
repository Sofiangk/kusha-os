<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        $query = Client::with('creator:id,name')
            ->latest();

        // Search by name or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Filter by frequent clients (recurring customers)
        if ($request->has('recurring')) {
            $query->where('total_orders', '>=', 3);
        }

        $clients = $query->paginate($request->get('per_page', 15));

        return $this->respond(['clients' => $clients], 'Clients retrieved successfully');
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:150',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'address_ar' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return $this->respond(['client' => $client], 'Client created successfully', 201);
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $client->load(['creator:id,name', 'orders']);

        return $this->respond(['client' => $client], 'Client retrieved successfully');
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:150',
            'phone' => 'sometimes|required|string|max:20|unique:clients,phone,' . $client->id,
            'address_ar' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return $this->respond(['client' => $client], 'Client updated successfully');
    }

    /**
     * Remove the specified client (soft delete)
     */
    public function destroy(Client $client)
    {
        // STRICT: Block if client has existing orders
        if ($client->hasOrders()) {
            $ordersCount = $client->orders()->withTrashed()->count();

            return $this->respondError(
                'Cannot delete client with existing orders',
                ['orders_count' => $ordersCount],
                409,
                'CLIENT_HAS_ORDERS'
            );
        }

        $client->delete();

        return $this->respond(['client' => $client], 'Client deleted successfully');
    }

    /**
     * Search for a client by phone (for quick lookup when creating orders)
     */
    public function searchByPhone(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $client = Client::where('phone', $validated['phone'])->first();

        if (!$client) {
            return $this->respondError('Client not found', ['phone' => $validated['phone']], 404, 'CLIENT_NOT_FOUND');
        }

        return $this->respond(['client' => $client], 'Client found');
    }
}
