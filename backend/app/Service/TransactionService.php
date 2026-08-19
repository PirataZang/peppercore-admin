<?php

namespace App\Service;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    /**
     * Paginated, filterable list of transactions across all projects (Financeiro screen).
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::query()->with([
            'project:id,client_id,name,client_name',
            'project.client:id,name,document',
            'client:id,name,document',
        ]);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('project', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('client_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderByDesc('reference_month')->paginate($perPage);
    }

    /**
     * List every transaction for a project, most recent reference month first.
     */
    public function listForProject(int $projectId): Collection
    {
        return Transaction::with('client:id,name,document')
            ->where('project_id', $projectId)
            ->orderByDesc('reference_month')
            ->get();
    }

    /**
     * Record a new transaction for a project.
     */
    public function create(int $projectId, array $data): Transaction
    {
        $data['project_id'] = $projectId;

        return Transaction::create($data);
    }

    /**
     * Record a new transaction, optionally without a project (Financeiro screen).
     */
    public function createStandalone(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update any transaction by ID, regardless of project.
     */
    public function update(int $transactionId, array $data): Transaction
    {
        $transaction = Transaction::findOrFail($transactionId);
        $transaction->update($data);
        return $transaction;
    }

    /**
     * Delete a transaction belonging to a project.
     */
    public function delete(int $projectId, int $transactionId): bool
    {
        $transaction = $this->findForProject($projectId, $transactionId);
        return $transaction->delete();
    }

    /**
     * Delete any transaction by ID, regardless of project.
     */
    public function deleteById(int $transactionId): bool
    {
        return Transaction::findOrFail($transactionId)->delete();
    }

    /**
     * Find a single transaction, scoped to its project.
     */
    public function findForProject(int $projectId, int $transactionId): Transaction
    {
        return Transaction::where('project_id', $projectId)->findOrFail($transactionId);
    }

    /**
     * Find any transaction by ID, regardless of project.
     */
    public function find(int $transactionId): Transaction
    {
        return Transaction::with([
            'project:id,client_id,name,client_name',
            'project.client:id,name,document',
            'client:id,name,document',
        ])->findOrFail($transactionId);
    }

    /**
     * Aggregate a project's payment history: totals, punctuality and a monthly series for charts.
     */
    public function summaryForProject(int $projectId): array
    {
        $transactions = $this->listForProject($projectId);
        $paid = $transactions->where('status', 'paid');

        return [
            'total_received' => (float) $paid->sum('amount'),
            'paid_count' => $paid->count(),
            'late_count' => $paid->filter(fn (Transaction $t) => $t->paid_late)->count(),
            'last_payment_at' => $paid->max('paid_at')?->toDateString(),
            'monthly' => $transactions
                ->sortBy('reference_month')
                ->map(fn (Transaction $t) => [
                    'reference_month' => $t->reference_month->toDateString(),
                    'amount' => (float) $t->amount,
                    'status' => $t->status,
                    'paid_late' => $t->paid_late,
                ])
                ->values(),
        ];
    }

    /**
     * Guard helper: ensure the project exists before touching its transactions.
     */
    public function ensureProjectExists(int $projectId): Project
    {
        return Project::findOrFail($projectId);
    }
}
