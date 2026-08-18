<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Maps the short, URL-friendly subject key to its model class.
     * Add an entry here whenever a new model adopts the Auditable trait.
     */
    private const SUBJECT_MAP = [
        'project' => Project::class,
        'client' => Client::class,
        'transaction' => Transaction::class,
    ];

    /**
     * Change history for a single record, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_type' => ['required', Rule::in(array_keys(self::SUBJECT_MAP))],
            'subject_id' => 'required|integer',
        ]);

        $modelClass = self::SUBJECT_MAP[$validated['subject_type']];
        $subject = $modelClass::findOrFail($validated['subject_id']);

        $activities = Activity::forSubject($subject)
            ->with('causer')
            ->latest()
            ->get();

        return response()->json($activities->map(fn (Activity $activity) => $this->format($activity)));
    }

    private function format(Activity $activity): array
    {
        $after = collect($activity->changes->get('attributes', []));
        $before = collect($activity->changes->get('old', []));

        $fields = $after->keys()->merge($before->keys())->unique();

        return [
            'id' => $activity->id,
            'event' => $activity->event,
            'causer_name' => $activity->causer?->name ?? 'Sistema',
            'created_at' => $activity->created_at,
            'changes' => $fields->map(fn (string $field) => [
                'field' => $field,
                'before' => $before->get($field),
                'after' => $after->get($field),
            ])->values(),
        ];
    }
}
