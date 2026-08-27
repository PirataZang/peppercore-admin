<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'phone', 'email', 'address', 'description',
    'document', 'zip_code', 'street_name', 'street_number', 'neighborhood', 'city', 'state', 'active',
])]
class Client extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
