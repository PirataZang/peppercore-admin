<?php

namespace App\Service;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    /**
     * Get a paginated list of projects.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Project::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('client_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('domain', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get a single project by ID.
     */
    public function index(int $id): Project
    {
        return Project::findOrFail($id);
    }

    /**
     * Create a new project.
     */
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    /**
     * Update an existing project.
     */
    public function update(int $id, array $data): Project
    {
        $project = Project::findOrFail($id);
        $project->update($data);
        return $project;
    }

    /**
     * Delete a project.
     */
    public function delete(int $id): bool
    {
        $project = Project::findOrFail($id);
        return $project->delete();
    }

    /**
     * Aggregate data for the dashboard: totals, revenue, breakdown by type and upcoming dues.
     */
    public function summary(): array
    {
        $projects = Project::all();
        $now = Carbon::now();

        $upcoming = $projects
            ->filter(fn (Project $p) => $p->due_day !== null)
            ->map(function (Project $p) use ($now) {
                $due = $this->nextDueDate((int) $p->due_day, $now);
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'client_name' => $p->client_name,
                    'due_day' => $p->due_day,
                    'monthly_value' => $p->monthly_value,
                    'payment_status' => $p->payment_status,
                    'days_until_due' => $now->copy()->startOfDay()->diffInDays($due, false),
                ];
            })
            ->sortBy('days_until_due')
            ->take(5)
            ->values();

        return [
            'total' => $projects->count(),
            'monthly_revenue' => (float) $projects->sum('monthly_value'),
            'overdue_count' => $projects->where('payment_status', 'atrasado')->count(),
            'by_type' => [
                'site' => $projects->where('type', 'site')->count(),
                'sistema' => $projects->where('type', 'sistema')->count(),
                'host' => $projects->where('type', 'host')->count(),
            ],
            'values' => $projects->map(fn (Project $p) => [
                'name' => $p->name,
                'monthly_value' => (float) $p->monthly_value,
            ])->values(),
            'upcoming_due' => $upcoming,
        ];
    }

    /**
     * Resolve the next occurrence of a day-of-month, clamped to each month's real length.
     */
    private function nextDueDate(int $dueDay, Carbon $now): Carbon
    {
        $today = $now->copy()->startOfDay();
        $thisMonth = $today->copy()->day(min($dueDay, $today->daysInMonth));

        if ($thisMonth->gte($today)) {
            return $thisMonth;
        }

        $nextMonth = $today->copy()->addMonthNoOverflow()->startOfMonth();
        return $nextMonth->day(min($dueDay, $nextMonth->daysInMonth));
    }
}
