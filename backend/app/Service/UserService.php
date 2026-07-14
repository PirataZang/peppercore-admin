<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Get a paginated list of users.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single user by ID.
     */
    public function index(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        
        return User::create($data);
    }

    /**
     * Update an existing user.
     */
    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);
        return $user;
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
