<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('roles')->get();
            return ApiResponse::success($users, 'User list retrieved');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve users', 500, ['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role'     => 'required|exists:roles,name',
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $user->assignRole($validated['role']);

            return ApiResponse::success($user->load('roles'), 'User created successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create user', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with('roles')->find($id);

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            return ApiResponse::success($user);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve user', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            $validated = $request->validate([
                'name'     => 'sometimes|required|string|max:255',
                'email'    => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id),
                ],
                'password' => 'nullable|min:6',
                'role'     => 'sometimes|required|exists:roles,name',
            ]);

            $user->update([
                'name'     => $validated['name'] ?? $user->name,
                'email'    => $validated['email'] ?? $user->email,
                'password' => isset($validated['password']) ? bcrypt($validated['password']) : $user->password,
            ]);

            if (isset($validated['role'])) {
                $user->syncRoles([$validated['role']]);
            }

            return ApiResponse::success($user->load('roles'), 'User updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update user', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            $user->delete();

            return ApiResponse::success(null, 'User deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete user', 500, ['error' => $e->getMessage()]);
        }
    }
}
