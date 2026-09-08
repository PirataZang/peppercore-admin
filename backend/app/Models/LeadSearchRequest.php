<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['city', 'latitude', 'longitude', 'radius_km', 'status', 'notes'])]
class LeadSearchRequest extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_km' => 'integer',
        ];
    }

    public function potentialLeads(): HasMany
    {
        return $this->hasMany(PotentialLead::class);
    }
}
