<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'reference_month',
    'amount',
    'due_date',
    'paid_at',
    'status',
    'payment_method',
    'gateway',
    'gateway_id',
    'gateway_status',
    'gateway_payload',
    'notes',
])]
class Transaction extends Model
{
    use Auditable;

    protected $appends = ['paid_late'];

    protected function casts(): array
    {
        return [
            'reference_month' => 'date',
            'due_date' => 'date',
            'paid_at' => 'date',
            'amount' => 'decimal:2',
            'gateway_payload' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function paidLate(): Attribute
    {
        return Attribute::get(
            fn () => $this->paid_at !== null && $this->due_date !== null && $this->paid_at->gt($this->due_date),
        );
    }
}
