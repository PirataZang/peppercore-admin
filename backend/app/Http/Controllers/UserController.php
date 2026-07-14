<?php

namespace App\Http\Controllers;

use App\Service\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = (int) $request->input('per_page', 15);
        
        $users = $this->userService->list($filters, $perPage);
        
        return response()->json($users);
    }

    /**
     * Display the specified user.
     */
    public function index(int $id): JsonResponse
    {
        $user = $this->userService->index($id);
        
        return response()->json($user);
    }

    /**
     * Store a newly created user.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = $this->userService->create($validated);

        return response()->json($user, 201);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        $user = $this->userService->update($id, $validated);

        return response()->json($user);
    }

    /**
     * Remove the specified user.
     */
    public function delete(int $id): JsonResponse
    {
        $this->userService->delete($id);
        
        return response()->json(['message' => 'User deleted successfully']);
    }
}
