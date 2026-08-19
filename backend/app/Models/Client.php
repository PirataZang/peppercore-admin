<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'phone', 'email', 'address', 'description',
    'document', 'zip_code', 'street_name', 'street_number', 'neighborhood', 'city', 'state',
])]
class Client extends Model
{
    use Auditable;

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
