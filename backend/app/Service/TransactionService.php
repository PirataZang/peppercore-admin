<?php

namespace App\Service;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    /**
     * List every transaction for a project, most recent reference month first.
     */
    public function listForProject(int $projectId): Collection
    {
        return Transaction::where('project_id', $projectId)
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
     * Delete a transaction belonging to a project.
     */
    public function delete(int $projectId, int $transactionId): bool
    {
        $transaction = Transaction::where('project_id', $projectId)->findOrFail($transactionId);
        return $transaction->delete();
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
