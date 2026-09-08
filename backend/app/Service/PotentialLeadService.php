<?php

namespace App\Service;

use App\Models\PotentialLead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PotentialLeadService
{
    /**
     * Get a paginated list of potential leads.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PotentialLead::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('address', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['lead_search_request_id'])) {
            $query->where('lead_search_request_id', $filters['lead_search_request_id']);
        }

        if (isset($filters['check']) && $filters['check'] !== '') {
            $query->where('check', (bool) $filters['check']);
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Get a single potential lead by ID.
     */
    public function index(int $id): PotentialLead
    {
        return PotentialLead::findOrFail($id);
    }

    /**
     * Create a single potential lead.
     */
    public function create(array $data): PotentialLead
    {
        return PotentialLead::create($data);
    }

    /**
     * Create many potential leads at once — used by the search agent after it
     * finds candidates on the web for a given lead search request.
     */
    public function bulkCreate(array $leads): array
    {
        return array_map(fn (array $lead) => PotentialLead::create($lead), $leads);
    }

    /**
     * Update an existing potential lead (e.g. toggling "check" once verified).
     */
    public function update(int $id, array $data): PotentialLead
    {
        $lead = PotentialLead::findOrFail($id);
        $lead->update($data);
        return $lead;
    }

    /**
     * Delete a potential lead.
     */
    public function delete(int $id): bool
    {
        $lead = PotentialLead::findOrFail($id);
        return $lead->delete();
    }
}
