<?php

namespace App\Http\Controllers;

use App\Service\LeadSearchRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadSearchRequestController extends Controller
{
    protected LeadSearchRequestService $leadSearchRequestService;

    public function __construct(LeadSearchRequestService $leadSearchRequestService)
    {
        $this->leadSearchRequestService = $leadSearchRequestService;
    }

    /**
     * Display a listing of lead search requests.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['status']);
        $perPage = (int) $request->input('per_page', 15);

        $requests = $this->leadSearchRequestService->list($filters, $perPage);

        return response()->json($requests);
    }

    /**
     * Display the specified lead search request.
     */
    public function index(int $id): JsonResponse
    {
        $leadSearchRequest = $this->leadSearchRequestService->index($id);

        return response()->json($leadSearchRequest);
    }

    /**
     * Store a newly created lead search request.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_km' => 'required|integer|min:1|max:200',
            'notes' => 'sometimes|nullable|string',
        ]);

        $leadSearchRequest = $this->leadSearchRequestService->create($validated);

        return response()->json($leadSearchRequest, 201);
    }

    /**
     * Update the specified lead search request (status/notes reported by the search agent).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,running,done,failed',
            'notes' => 'sometimes|nullable|string',
        ]);

        $leadSearchRequest = $this->leadSearchRequestService->update($id, $validated);

        return response()->json($leadSearchRequest);
    }

    /**
     * Remove the specified lead search request.
     */
    public function delete(int $id): JsonResponse
    {
        $this->leadSearchRequestService->delete($id);

        return response()->json(['message' => 'Lead search request deleted successfully']);
    }
}
