<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::where('company_id', $request->user()->company_id)
                    ->with('userRole')
                    ->paginate(15);

        return response()->json([
            'users' => $users,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);

        return response()->json([
            'user' => $user->load('userRole'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'job_title' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($validator->validated());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('userRole'),
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,manager,staff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userRole = $user->userRole;
        if (!$userRole) {
            $userRole = UserRole::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'role' => $request->role,
            ]);
        } else {
            $userRole->update(['role' => $request->role]);
        }

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->load('userRole'),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function deactivate(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => false]);

        return response()->json([
            'message' => 'User deactivated successfully',
        ]);
    }

    public function activate(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => true]);

        return response()->json([
            'message' => 'User activated successfully',
        ]);
    }
}