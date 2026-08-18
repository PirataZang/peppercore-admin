<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Seed sample projects (and a bit of payment history) to preview the Projects dashboard.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'BotRPG',
                'type' => 'host',
                'client_name' => 'Interno',
                'monthly_value' => 0,
                'payment_status' => 'pago',
                'due_day' => null,
            ],
            [
                'name' => 'LaravelSync',
                'type' => 'sistema',
                'client_name' => 'Interno',
                'monthly_value' => 0,
                'payment_status' => 'pago',
                'due_day' => null,
            ],
            [
                'name' => 'PepperCore-site',
                'type' => 'site',
                'client_name' => 'PepperCore',
                'monthly_value' => 50,
                'payment_status' => 'pago',
                'due_day' => 10,
            ],
            [
                'name' => 'pillarq',
                'type' => 'sistema',
                'client_name' => 'Interno',
                'monthly_value' => 0,
                'payment_status' => 'pago',
                'due_day' => null,
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::updateOrCreate(['name' => $data['name']], $data);

            if ($project->name === 'PepperCore-site' && $project->transactions()->count() === 0) {
                $this->seedSampleHistory($project);
            }
        }
    }

    private function seedSampleHistory(Project $project): void
    {
        $months = [
            ['offset' => 2, 'paidOffsetDays' => -1, 'status' => 'paid'],
            ['offset' => 1, 'paidOffsetDays' => 4, 'status' => 'paid'],
            ['offset' => 0, 'paidOffsetDays' => null, 'status' => 'pending'],
        ];

        foreach ($months as $m) {
            $reference = Carbon::now()->startOfMonth()->subMonths($m['offset']);
            $due = $reference->copy()->day($project->due_day);

            $project->transactions()->create([
                'reference_month' => $reference->toDateString(),
                'amount' => $project->monthly_value,
                'due_date' => $due->toDateString(),
                'paid_at' => $m['paidOffsetDays'] !== null ? $due->copy()->addDays($m['paidOffsetDays'])->toDateString() : null,
                'status' => $m['status'],
                'payment_method' => $m['status'] === 'paid' ? 'pix' : null,
            ]);
        }
    }
}
