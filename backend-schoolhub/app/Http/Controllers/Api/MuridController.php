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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nis' => 'required|string|unique:murids,nis',
            'nama_lengkap_murid' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'nullable|string|max:255',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'nama_orangtua' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_telepon_ortu' => 'nullable|string|max:20',
            'agama' => 'nullable|string|max:50',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
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
                'name' => $request->nama_lengkap_murid,
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
                'Nama_lengkap_murid' => $request->nama_lengkap_murid,
                'gender' => $request->gender,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tempat_lahir' => $request->tempat_lahir,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
                'nama_orangtua' => $request->nama_orangtua,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'nomor_telepon_ortu' => $request->nomor_telepon_ortu,
                'agama' => $request->agama,
                'anak_ke' => $request->anak_ke,
                'jumlah_saudara' => $request->jumlah_saudara,
                'hobi' => $request->hobi,
                'cita_cita' => $request->cita_cita,
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
     * Profil murid yang sedang login.
     * PENTING: route ini harus didaftarkan SEBELUM /{id} di routes/api.php,
     * kalau tidak, 'profile' akan tertangkap sebagai nilai {id}.
     */
    public function myProfile(Request $request): JsonResponse
    {
        $murid = Murid::with('kelas')->where('user_id', $request->user()->id)->first();

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        // Kolom database memakai 'Nama_lengkap_murid' (N besar).
        // Disamakan ke 'nama_lengkap_murid' supaya cocok dengan frontend.
        $data = $murid->toArray();
        $data['nama_lengkap_murid'] = $murid->Nama_lengkap_murid;

        return response()->json([
            'success' => true,
            'message' => 'Profil murid berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Update profil murid yang sedang login.
     * Murid hanya boleh mengubah datanya sendiri, bukan nis/kelas_id.
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        $murid = Murid::where('user_id', $request->user()->id)->first();

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap_murid' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'agama' => 'nullable|string|max:50',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'nomor_telepon' => 'nullable|string|max:20',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'alamat' => 'nullable|string',
            'nama_orangtua' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_telepon_ortu' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Samakan lagi ke nama kolom asli sebelum disimpan.
        $validated['Nama_lengkap_murid'] = $validated['nama_lengkap_murid'];
        unset($validated['nama_lengkap_murid']);

        DB::beginTransaction();
        try {
            $murid->update($validated);

            // Nama di tabel users ikut disamakan (opsional, biar konsisten).
            $murid->user?->update(['name' => $validated['Nama_lengkap_murid']]);

            DB::commit();

            $murid->refresh()->load('kelas');
            $data = $murid->toArray();
            $data['nama_lengkap_murid'] = $murid->Nama_lengkap_murid;

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
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
            'email' => 'sometimes|email|unique:users,email,' . $murid->user_id,
            'password' => 'sometimes|string|min:8',
            'nis' => 'sometimes|string|unique:murids,nis,' . $murid->id,
            'nama_lengkap_murid' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:L,P',
            'kelas_id' => 'sometimes|exists:kelas,id',
            'tanggal_lahir' => 'sometimes|date',
            'tempat_lahir' => 'nullable|string|max:255',
            'alamat' => 'sometimes|string',
            'nomor_telepon' => 'sometimes|string|max:20',
            'nama_orangtua' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_telepon_ortu' => 'nullable|string|max:20',
            'agama' => 'nullable|string|max:50',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
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
            if ($request->has('nama_lengkap_murid')) $userData['name'] = $request->nama_lengkap_murid;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->filled('password')) $userData['password'] = Hash::make($request->password);

            if (!empty($userData)) {
                $murid->user->update($userData);
            }

            // Update murid profile
            $muridData = $request->only([
                'kelas_id',
                'nis',
                'nama_lengkap_murid',
                'gender',
                'tanggal_lahir',
                'tempat_lahir',
                'alamat',
                'nomor_telepon',
                'nama_orangtua',
                'nama_ayah',
                'nama_ibu',
                'pekerjaan_ayah',
                'pekerjaan_ibu',
                'nomor_telepon_ortu',
                'agama',
                'anak_ke',
                'jumlah_saudara',
                'hobi',
                'cita_cita',
            ]);

            // Use Nama_lengkap_murid (with capital N) for database field
            if (isset($muridData['nama_lengkap_murid'])) {
                $muridData['Nama_lengkap_murid'] = $muridData['nama_lengkap_murid'];
                unset($muridData['nama_lengkap_murid']);
            }

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
