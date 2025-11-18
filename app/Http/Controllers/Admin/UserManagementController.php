<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('limit', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'page' => $users->currentPage(),
                'limit' => $users->perPage(),
                'totalPages' => $users->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,editor,viewer,user',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'createdAt' => $user->created_at->toISOString(),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'lastLogin' => $user->last_login?->toISOString(),
            'createdAt' => $user->created_at->toISOString(),
            'updatedAt' => $user->updated_at->toISOString(),
            'permissions' => $this->getUserPermissions($user->role),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:admin,editor,viewer,user',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $user->update($validated);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'updatedAt' => $user->updated_at->toISOString(),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'error' => 'Cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'id' => $user->id
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'newPassword' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($validated['newPassword']),
        ]);

        return response()->json([
            'message' => 'Password reset successfully',
            'userId' => $user->id
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,editor,viewer',
        ]);

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'id' => $user->id,
            'role' => $user->role,
            'updatedAt' => $user->updated_at->toISOString(),
        ]);
    }

    private function getUserPermissions(string $role): array
    {
        $permissions = [
            'admin' => ['*'],
            'editor' => ['projects.create', 'projects.edit', 'projects.view', 'reports.view'],
            'viewer' => ['projects.view', 'reports.view'],
        ];

        return $permissions[$role] ?? [];
    }
}