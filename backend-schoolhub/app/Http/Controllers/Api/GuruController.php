<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    /**
     * Display a listing of guru.
     */
    public function index(): JsonResponse
    {
        $gurus = Guru::with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil diambil',
            'data' => $gurus,
        ]);
    }

    /**
     * Store a newly created guru.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nip' => 'required|string|unique:gurus,nip',
            'nama_lengkap_guru' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Guru',
                'is_active' => true,
            ]);

            // Create guru profile
            $guru = Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_lengkap_guru' => $request->nama_lengkap_guru,
                'gender' => $request->gender,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil ditambahkan',
                'data' => $guru->load('user'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified guru.
     */
    public function show($id): JsonResponse
    {
        $guru = Guru::with('user')->find($id);

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail guru berhasil diambil',
            'data' => $guru,
        ]);
    }

    /**
     * Update the specified guru.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $guru->user_id,
            'password' => 'sometimes|string|min:8',
            'nip' => 'sometimes|string|unique:gurus,nip,' . $guru->id,
            'nama_lengkap_guru' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:L,P',
            'tanggal_lahir' => 'sometimes|date',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update user
            $userData = [];
            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('password')) $userData['password'] = Hash::make($request->password);

            if (!empty($userData)) {
                $guru->user->update($userData);
            }

            // Update guru profile
            $guruData = $request->only([
                'nip',
                'nama_lengkap_guru',
                'gender',
                'tanggal_lahir',
                'alamat',
                'nomor_telepon',
            ]);

            $guru->update($guruData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil diupdate',
                'data' => $guru->load('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified guru.
     */
    public function destroy($id): JsonResponse
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $guru->user->delete(); // Will cascade delete guru
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
