<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    /**
     * Display a listing of mapel.
     */
    public function index(): JsonResponse
    {
        $mapels = DB::table('mapels')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel berhasil diambil',
            'data' => $mapels,
        ]);
    }

    /**
     * Store a newly created mapel.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_mapel' => 'required|string|unique:mapels,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jumlah_jam' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mapel = DB::table('mapels')->insertGetId([
            'kode_mapel' => $request->kode_mapel,
            'nama_mapel' => $request->nama_mapel,
            'deskripsi' => $request->deskripsi,
            'jumlah_jam' => $request->jumlah_jam,
            'is_active' => $request->is_active ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = DB::table('mapels')->find($mapel);

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil dibuat',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified mapel.
     */
    public function show($id): JsonResponse
    {
        $mapel = DB::table('mapels')->find($id);

        if (!$mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail mapel berhasil diambil',
            'data' => $mapel,
        ]);
    }

    /**
     * Update the specified mapel.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $mapel = DB::table('mapels')->find($id);

        if (!$mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_mapel' => 'sometimes|string|unique:mapels,kode_mapel,' . $id,
            'nama_mapel' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'jumlah_jam' => 'sometimes|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('mapels')->where('id', $id)->update([
            'kode_mapel' => $request->kode_mapel ?? $mapel->kode_mapel,
            'nama_mapel' => $request->nama_mapel ?? $mapel->nama_mapel,
            'deskripsi' => $request->deskripsi ?? $mapel->deskripsi,
            'jumlah_jam' => $request->jumlah_jam ?? $mapel->jumlah_jam,
            'is_active' => $request->is_active ?? $mapel->is_active,
            'updated_at' => now(),
        ]);

        $data = DB::table('mapels')->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil diupdate',
            'data' => $data,
        ]);
    }

    /**
     * Remove the specified mapel.
     */
    public function destroy($id): JsonResponse
    {
        $mapel = DB::table('mapels')->find($id);

        if (!$mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak ditemukan',
            ], 404);
        }

        DB::table('mapels')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil dihapus',
        ]);
    }

    /**
     * Get active mapel only.
     */
    public function active(): JsonResponse
    {
        $mapels = DB::table('mapels')->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel aktif berhasil diambil',
            'data' => $mapels,
        ]);
    }

    /**
     * Assign mapel to kelas.
     */
    public function assignToKelas(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if already assigned
        $exists = DB::table('class_subjects')
            ->where('kelas_id', $request->kelas_id)
            ->where('mapel_id', $request->mapel_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel sudah di-assign ke kelas ini',
            ], 422);
        }

        DB::table('class_subjects')->insert([
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil di-assign ke kelas',
        ], 201);
    }

    /**
     * Assign mapel to guru.
     */
    public function assignToGuru(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if already assigned
        $exists = DB::table('teacher_subjects')
            ->where('guru_id', $request->guru_id)
            ->where('mapel_id', $request->mapel_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel sudah di-assign ke guru ini',
            ], 422);
        }

        DB::table('teacher_subjects')->insert([
            'guru_id' => $request->guru_id,
            'mapel_id' => $request->mapel_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil di-assign ke guru',
        ], 201);
    }

    /**
     * Get mapel by kelas.
     */
    public function byKelas($kelasId): JsonResponse
    {
        $mapels = DB::table('mapels')
            ->join('class_subjects', 'mapels.id', '=', 'class_subjects.mapel_id')
            ->where('class_subjects.kelas_id', $kelasId)
            ->select('mapels.*')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel berdasarkan kelas berhasil diambil',
            'data' => $mapels,
        ]);
    }

    /**
     * Get mapel by guru.
     */
    public function byGuru($guruId): JsonResponse
    {
        $mapels = DB::table('mapels')
            ->join('teacher_subjects', 'mapels.id', '=', 'teacher_subjects.mapel_id')
            ->where('teacher_subjects.guru_id', $guruId)
            ->select('mapels.*')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel berdasarkan guru berhasil diambil',
            'data' => $mapels,
        ]);
    }
}
