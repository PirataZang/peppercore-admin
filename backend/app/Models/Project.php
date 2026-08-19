<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'type',
    'domain',
    'client_id',
    'client_name',
    'client_contact',
    'monthly_value',
    'due_day',
    'payment_status',
    'description',
])]
class Project extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'monthly_value' => 'decimal:2',
            'due_day' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
