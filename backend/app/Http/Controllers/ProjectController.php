<?php

namespace App\Http\Controllers;

use App\Service\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Display a listing of projects.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = (int) $request->input('per_page', 15);

        $projects = $this->projectService->list($filters, $perPage);

        return response()->json($projects);
    }

    /**
     * Display the specified project.
     */
    public function index(int $id): JsonResponse
    {
        $project = $this->projectService->index($id);

        return response()->json($project);
    }

    /**
     * Store a newly created project.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $project = $this->projectService->create($validated);

        return response()->json($project, 201);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate($this->rules(sometimes: true));

        $project = $this->projectService->update($id, $validated);

        return response()->json($project);
    }

    /**
     * Aggregated data for the dashboard (totals, revenue, breakdown by type, upcoming dues).
     */
    public function summary(): JsonResponse
    {
        return response()->json($this->projectService->summary());
    }

    /**
     * Remove the specified project.
     */
    public function delete(int $id): JsonResponse
    {
        $this->projectService->delete($id);

        return response()->json(['message' => 'Project deleted successfully']);
    }

    private function rules(bool $sometimes = false): array
    {
        $required = $sometimes ? 'sometimes' : 'required';

        return [
            'name' => "{$required}|string|max:255",
            'type' => "{$required}|in:site,sistema,host",
            'domain' => 'sometimes|nullable|string|max:255',
            'client_name' => "{$required}|string|max:255",
            'client_contact' => 'sometimes|nullable|string|max:255',
            'monthly_value' => 'sometimes|nullable|numeric|min:0',
            'due_day' => 'sometimes|nullable|integer|min:1|max:31',
            'payment_status' => 'sometimes|in:pago,pendente,atrasado',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
