<?php

namespace App\Http\Controllers;

use App\Enums\GatewayProvider;
use App\Enums\PaymentMethod;
use App\Models\Transaction;
use App\Service\MercadoPagoService;
use App\Service\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected MercadoPagoService $mercadoPagoService,
    ) {
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

        $validated = $request->validate($this->rules());

        $transaction = $this->transactionService->create($projectId, $validated);

        return response()->json($transaction, 201);
    }

    public function destroy(int $projectId, int $transactionId): JsonResponse
    {
        $this->transactionService->delete($projectId, $transactionId);

        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    /**
     * Create a Pix, Boleto or Card charge (an invoice with a real due date) for a transaction.
     */
    public function charge(Request $request, int $projectId, int $transactionId): JsonResponse
    {
        $this->transactionService->ensureProjectExists($projectId);

        $transaction = $this->transactionService->findForProject($projectId, $transactionId);

        return $this->processCharge($request, $transaction);
    }

    /**
     * Paginated, filterable list of transactions across all projects (Financeiro screen).
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'project_id', 'status']);
        $perPage = (int) $request->input('per_page', 15);

        return response()->json($this->transactionService->list($filters, $perPage));
    }

    /**
     * Show a single transaction, regardless of project.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json($this->transactionService->find($id));
    }

    /**
     * Create a transaction, optionally without a project (Financeiro screen).
     */
    public function storeStandalone(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules() + [
            'project_id' => 'sometimes|nullable|integer|exists:projects,id',
        ]);

        $transaction = $this->transactionService->createStandalone($validated);

        return response()->json($transaction, 201);
    }

    /**
     * Update any transaction by ID, regardless of project.
     */
    public function updateStandalone(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate($this->rules(sometimes: true) + [
            'project_id' => 'sometimes|nullable|integer|exists:projects,id',
        ]);

        $transaction = $this->transactionService->update($id, $validated);

        return response()->json($transaction);
    }

    /**
     * Delete any transaction by ID, regardless of project.
     */
    public function destroyStandalone(int $id): JsonResponse
    {
        $this->transactionService->deleteById($id);

        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    /**
     * Create a Pix, Boleto or Card charge for a transaction, regardless of project.
     */
    public function chargeStandalone(Request $request, int $id): JsonResponse
    {
        $transaction = $this->transactionService->find($id);

        return $this->processCharge($request, $transaction);
    }

    /**
     * Return the Public Key needed by the frontend to initialize the Mercado Pago SDK JS
     * (required to securely tokenize card data before charging a transaction by card).
     */
    public function mercadoPagoPublicKey(): JsonResponse
    {
        try {
            return response()->json(['public_key' => $this->mercadoPagoService->publicKey()]);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    private function processCharge(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'client_id' => 'sometimes|nullable|integer|exists:clients,id',
            'card' => 'required_if:method,credit_card|array',
            'card.token' => 'required_if:method,credit_card|string',
            'card.payment_method_id' => 'required_if:method,credit_card|string',
            'card.issuer_id' => 'sometimes|nullable|string',
            'card.installments' => 'sometimes|nullable|integer|min:1',
        ]);

        $method = PaymentMethod::from($validated['method']);

        if ($request->filled('client_id') && (int) $validated['client_id'] !== $transaction->client_id) {
            $transaction->client_id = $validated['client_id'];
            $transaction->save();
        }

        try {
            $transaction = $this->mercadoPagoService->createCharge($transaction, $method, $validated['card'] ?? []);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json($transaction);
    }

    private function rules(bool $sometimes = false): array
    {
        $required = $sometimes ? 'sometimes' : 'required';

        return [
            'reference_month' => "{$required}|date",
            'amount' => "{$required}|numeric|min:0",
            'due_date' => "{$required}|date",
            'paid_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|in:pending,paid,failed,refunded',
            'payment_method' => ['sometimes', 'nullable', Rule::enum(PaymentMethod::class)],
            'gateway' => ['sometimes', 'nullable', Rule::enum(GatewayProvider::class)],
            'gateway_id' => 'sometimes|nullable|string|max:255',
            'gateway_status' => 'sometimes|nullable|string|max:100',
            'notes' => 'sometimes|nullable|string',
        ];
    }
}
