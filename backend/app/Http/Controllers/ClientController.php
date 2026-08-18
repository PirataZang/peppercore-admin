<?php

namespace App\Http\Controllers;

use App\Service\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Display a listing of clients.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = (int) $request->input('per_page', 15);

        $clients = $this->clientService->list($filters, $perPage);

        return response()->json($clients);
    }

    /**
     * Display the specified client.
     */
    public function index(int $id): JsonResponse
    {
        $client = $this->clientService->index($id);

        return response()->json($client);
    }

    /**
     * Store a newly created client.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:30',
            'email' => 'sometimes|nullable|string|email|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $client = $this->clientService->create($validated);

        return response()->json($client, 201);
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:30',
            'email' => 'sometimes|nullable|string|email|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $client = $this->clientService->update($id, $validated);

        return response()->json($client);
    }

    /**
     * Remove the specified client.
     */
    public function delete(int $id): JsonResponse
    {
        $this->clientService->delete($id);

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
