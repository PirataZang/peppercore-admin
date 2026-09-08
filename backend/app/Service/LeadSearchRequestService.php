<?php

namespace App\Service;

use App\Models\LeadSearchRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeadSearchRequestService
{
    /**
     * Get a paginated list of lead search requests, most recent first.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LeadSearchRequest::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Get a single lead search request by ID.
     */
    public function index(int $id): LeadSearchRequest
    {
        return LeadSearchRequest::findOrFail($id);
    }

    /**
     * Create a new lead search request (starts as "pending" until an agent processes it).
     */
    public function create(array $data): LeadSearchRequest
    {
        $data['status'] = 'pending';

        return LeadSearchRequest::create($data);
    }

    /**
     * Update an existing lead search request (used by the search agent to report status).
     */
    public function update(int $id, array $data): LeadSearchRequest
    {
        $request = LeadSearchRequest::findOrFail($id);
        $request->update($data);
        return $request;
    }

    /**
     * Delete a lead search request.
     */
    public function delete(int $id): bool
    {
        $request = LeadSearchRequest::findOrFail($id);
        return $request->delete();
    }
}
