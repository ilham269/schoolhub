<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // User tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // User tidak aktif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif.',
            ], 403);
        }

        // Password salah
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Hapus token lama
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken(
            'schoolhub-token'
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',

            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Data user yang sedang login
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}