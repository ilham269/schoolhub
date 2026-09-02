<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /**
     * Display a listing of karyawan.
     */
    public function index(): JsonResponse
    {
        $karyawans = Karyawan::with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diambil',
            'data' => $karyawans,
        ]);
    }

    /**
     * Store a newly created karyawan.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nip' => 'nullable|string|unique:karyawans,nip',
            'nama_lengkap_karyawan' => 'required|string|max:255',
            'bagian' => 'required|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
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
                'role' => 'Karyawan',
                'is_active' => true,
            ]);

            // Create karyawan profile
            $karyawan = Karyawan::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_lengkap_karyawan' => $request->nama_lengkap_karyawan,
                'bagian' => $request->bagian,
                'nomor_telepon' => $request->nomor_telepon,
                'alamat' => $request->alamat,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan',
                'data' => $karyawan->load('user'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan karyawan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified karyawan.
     */
    public function show($id): JsonResponse
    {
        $karyawan = Karyawan::with('user')->find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil',
            'data' => $karyawan,
        ]);
    }

    /**
     * Update the specified karyawan.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $karyawan = Karyawan::find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $karyawan->user_id,
            'password' => 'sometimes|string|min:8',
            'nip' => 'nullable|string|unique:karyawans,nip,' . $karyawan->id,
            'nama_lengkap_karyawan' => 'sometimes|string|max:255',
            'bagian' => 'sometimes|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
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
                $karyawan->user->update($userData);
            }

            // Update karyawan profile
            $karyawanData = $request->only([
                'nip',
                'nama_lengkap_karyawan',
                'bagian',
                'nomor_telepon',
                'alamat',
            ]);

            $karyawan->update($karyawanData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diupdate',
                'data' => $karyawan->load('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate karyawan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified karyawan.
     */
    public function destroy($id): JsonResponse
    {
        $karyawan = Karyawan::find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $karyawan->user->delete(); // Will cascade delete karyawan
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get karyawans by bagian.
     */
    public function byBagian($bagian): JsonResponse
    {
        $karyawans = Karyawan::where('bagian', $bagian)
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Data karyawan bagian {$bagian} berhasil diambil",
            'data' => $karyawans,
        ]);
    }
}
