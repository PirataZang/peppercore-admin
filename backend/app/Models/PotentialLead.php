<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'address', 'phone', 'check', 'lead_search_request_id'])]
class PotentialLead extends Model
{
    protected function casts(): array
    {
        return [
            'check' => 'boolean',
        ];
    }

    public function leadSearchRequest(): BelongsTo
    {
        return $this->belongsTo(LeadSearchRequest::class);
    }
}
