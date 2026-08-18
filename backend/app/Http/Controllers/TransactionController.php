<?php

namespace App\Http\Controllers;

use App\Service\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
    }

    public function index(int $projectId): JsonResponse
    {
        $this->transactionService->ensureProjectExists($projectId);

        return response()->json($this->transactionService->listForProject($projectId));
    }

    public function summary(int $projectId): JsonResponse
    {
        $this->transactionService->ensureProjectExists($projectId);

        return response()->json($this->transactionService->summaryForProject($projectId));
    }

    public function store(Request $request, int $projectId): JsonResponse
    {
        $this->transactionService->ensureProjectExists($projectId);

        $validated = $request->validate([
            'reference_month' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'paid_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|in:pending,paid,failed,refunded',
            'payment_method' => 'sometimes|nullable|string|max:100',
            'gateway' => 'sometimes|nullable|string|max:100',
            'gateway_id' => 'sometimes|nullable|string|max:255',
            'gateway_status' => 'sometimes|nullable|string|max:100',
            'notes' => 'sometimes|nullable|string',
        ]);

        $transaction = $this->transactionService->create($projectId, $validated);

        return response()->json($transaction, 201);
    }

    public function destroy(int $projectId, int $transactionId): JsonResponse
    {
        $this->transactionService->delete($projectId, $transactionId);

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
