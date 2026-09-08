<?php

namespace App\Http\Controllers;

use App\Service\PotentialLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PotentialLeadController extends Controller
{
    protected PotentialLeadService $potentialLeadService;

    public function __construct(PotentialLeadService $potentialLeadService)
    {
        $this->potentialLeadService = $potentialLeadService;
    }

    /**
     * Display a listing of potential leads.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'lead_search_request_id', 'check']);
        $perPage = (int) $request->input('per_page', 15);

        $leads = $this->potentialLeadService->list($filters, $perPage);

        return response()->json($leads);
    }

    /**
     * Display the specified potential lead.
     */
    public function index(int $id): JsonResponse
    {
        $lead = $this->potentialLeadService->index($id);

        return response()->json($lead);
    }

    /**
     * Store a newly created potential lead.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $lead = $this->potentialLeadService->create($validated);

        return response()->json($lead, 201);
    }

    /**
     * Store many potential leads at once (used by the search agent).
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leads' => 'required|array|min:1',
            'leads.*.name' => 'required|string|max:255',
            'leads.*.address' => 'sometimes|nullable|string|max:255',
            'leads.*.phone' => 'sometimes|nullable|string|max:30',
            'leads.*.lead_search_request_id' => 'sometimes|nullable|integer|exists:lead_search_requests,id',
        ]);

        $leads = $this->potentialLeadService->bulkCreate($validated['leads']);

        return response()->json(['leads' => $leads], 201);
    }

    /**
     * Update the specified potential lead.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate($this->rules(sometimes: true));

        $lead = $this->potentialLeadService->update($id, $validated);

        return response()->json($lead);
    }

    /**
     * Remove the specified potential lead.
     */
    public function delete(int $id): JsonResponse
    {
        $this->potentialLeadService->delete($id);

        return response()->json(['message' => 'Potential lead deleted successfully']);
    }

    private function rules(bool $sometimes = false): array
    {
        $required = $sometimes ? 'sometimes' : 'required';

        return [
            'name' => "{$required}|string|max:255",
            'address' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:30',
            'check' => 'sometimes|boolean',
            'lead_search_request_id' => 'sometimes|nullable|integer|exists:lead_search_requests,id',
        ];
    }
}
