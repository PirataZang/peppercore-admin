<?php

namespace App\Http\Controllers;

use App\Service\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Public, unauthenticated client sign-up — meant to be called from a form
     * embedded on the PepperCore marketing site. Only accepts the fields a
     * visitor can fill in themselves; "active" is always forced to true.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:30',
            'email' => 'sometimes|nullable|string|email|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'document' => 'sometimes|nullable|string|max:20',
        ]);

        $validated['active'] = true;

        $client = $this->clientService->create($validated);

        return response()->json([
            'message' => 'Cadastro recebido com sucesso!',
            'id' => $client->id,
        ], 201);
    }
}
