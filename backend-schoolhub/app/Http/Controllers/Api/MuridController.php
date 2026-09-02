<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Murid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MuridController extends Controller
{
    /**
     * Display a listing of murid.
     */
    public function index(): JsonResponse
    {
        $murids = Murid::with(['user', 'kelas'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Data murid berhasil diambil',
            'data' => $murids,
        ]);
    }

    /**
     * Store a newly created murid.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required|string|unique:murids,nis',
            'Nama_lengkap_murid' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
            'nama_orangtua' => 'nullable|string|max:255',
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
                'role' => 'Murid',
                'is_active' => true,
            ]);

            // Create murid profile
            $murid = Murid::create([
                'user_id' => $user->id,
                'kelas_id' => $request->kelas_id,
                'nis' => $request->nis,
                'Nama_lengkap_murid' => $request->Nama_lengkap_murid,
                'gender' => $request->gender,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
                'nama_orangtua' => $request->nama_orangtua,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Murid berhasil ditambahkan',
                'data' => $murid->load(['user', 'kelas']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan murid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified murid.
     */
    public function show($id): JsonResponse
    {
        $murid = Murid::with(['user', 'kelas'])->find($id);

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail murid berhasil diambil',
            'data' => $murid,
        ]);
    }

    /**
     * Update the specified murid.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $murid = Murid::find($id);

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $murid->user_id,
            'password' => 'sometimes|string|min:8',
            'kelas_id' => 'sometimes|exists:kelas,id',
            'nis' => 'sometimes|string|unique:murids,nis,' . $murid->id,
            'Nama_lengkap_murid' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:L,P',
            'tanggal_lahir' => 'sometimes|date',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
            'nama_orangtua' => 'nullable|string|max:255',
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
                $murid->user->update($userData);
            }

            // Update murid profile
            $muridData = $request->only([
                'kelas_id',
                'nis',
                'Nama_lengkap_murid',
                'gender',
                'tanggal_lahir',
                'alamat',
                'nomor_telepon',
                'nama_orangtua',
            ]);

            $murid->update($muridData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Murid berhasil diupdate',
                'data' => $murid->load(['user', 'kelas']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate murid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified murid.
     */
    public function destroy($id): JsonResponse
    {
        $murid = Murid::find($id);

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $murid->user->delete(); // Will cascade delete murid
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Murid berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus murid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get murids by kelas.
     */
    public function byKelas($kelasId): JsonResponse
    {
        $murids = Murid::where('kelas_id', $kelasId)
            ->with(['user', 'kelas'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data murid berdasarkan kelas berhasil diambil',
            'data' => $murids,
        ]);
    }
}
