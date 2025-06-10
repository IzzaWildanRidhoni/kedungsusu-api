<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            // Validasi manual agar bisa dikontrol error-nya
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation error', 422, $validator->errors());
            }

            // Simpan user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Assign default role
            $user->assignRole('user');

            return ApiResponse::success([
                'user' => $user,
            ], 'User registered successfully');
        } catch (Exception $e) {
            // Optional: log error
            Log::error('Register error: ' . $e->getMessage());

            return ApiResponse::error('Something went wrong', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation error', 422, $validator->errors());
            }

            // Cari user
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            // Buat token
            $token = $user->createToken('api-token')->plainTextToken;

            // Ambil role name dan sembunyikan properti "roles" dari user
            $role = $user->getRoleNames()->first(); // "user" / "admin"
            $user->role = $role;
            unset($user->roles); // Hapus kalau sebelumnya pakai load('roles')

            return ApiResponse::success([
                'token' => $token,
                'user' => $user,
            ], 'Login successful');
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return ApiResponse::error('Something went wrong', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('User not authenticated', 401);
            }

            // Tambahkan role ke user
            $user->role = $user->getRoleNames()->first();

            return ApiResponse::success($user, 'User profile fetched');
        } catch (\Exception $e) {
            Log::error('Fetch user error: ' . $e->getMessage());

            return ApiResponse::error('Failed to get user', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('Logout gagal: token tidak ditemukan atau sudah tidak valid', 401);
            }

            $token = $user->currentAccessToken();

            if (!$token) {
                return ApiResponse::error('Logout gagal: token sudah dicabut atau tidak valid', 401);
            }

            $token->delete();

            return ApiResponse::success(null, 'Logout berhasil');
        } catch (\Throwable $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('Logout gagal: terjadi kesalahan server', 500, [
                'error' => 'Internal server error'
            ]);
        }
    }
}
