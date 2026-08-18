<?php

namespace App\Service;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientService
{
    /**
     * Get a paginated list of clients.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Client::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get a single client by ID.
     */
    public function index(int $id): Client
    {
        return Client::findOrFail($id);
    }

    /**
     * Create a new client.
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Update an existing client.
     */
    public function update(int $id, array $data): Client
    {
        $client = Client::findOrFail($id);
        $client->update($data);
        return $client;
    }

    /**
     * Delete a client.
     */
    public function delete(int $id): bool
    {
        $client = Client::findOrFail($id);
        return $client->delete();
    }
}
