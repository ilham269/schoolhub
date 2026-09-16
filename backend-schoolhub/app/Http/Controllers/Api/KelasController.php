<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    /**
     * Display a listing of kelas.
     */
    public function index(): JsonResponse
    {
        $kelas = Kelas::with(['murids', 'waliKelas'])->get()->map(function ($k) {
            return [
                'id' => $k->id,
                'name' => $k->name,
                'kelas' => $k->kelas,
                'jurusan' => $k->jurusan,
                'angkatan' => $k->angkatan,
                'wali_kelas' => $k->waliKelas ? $k->waliKelas->name : null,
                'wali_kelas_id' => $k->wali_kelas,
                'kapasitas' => $k->kapasitas ?? 36,
                'jumlah_siswa' => $k->murids->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data kelas berhasil diambil',
            'data' => $kelas,
        ]);
    }

    /**
     * Store a newly created kelas.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'kelas' => 'required|string|max:10',
            'jurusan' => 'required|string|max:50',
            'angkatan' => 'required|integer|min:2000|max:2100',
            'wali_kelas' => 'nullable|exists:users,id',
            'kapasitas' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kelas = Kelas::create($request->all());
        $kelas->load(['murids', 'waliKelas']);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan',
            'data' => [
                'id' => $kelas->id,
                'name' => $kelas->name,
                'kelas' => $kelas->kelas,
                'jurusan' => $kelas->jurusan,
                'angkatan' => $kelas->angkatan,
                'wali_kelas' => $kelas->waliKelas ? $kelas->waliKelas->name : null,
                'wali_kelas_id' => $kelas->wali_kelas,
                'kapasitas' => $kelas->kapasitas ?? 36,
                'jumlah_siswa' => $kelas->murids->count(),
            ],
        ], 201);
    }

    /**
     * Display the specified kelas.
     */
    public function show($id): JsonResponse
    {
        $kelas = Kelas::with('murids')->find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kelas berhasil diambil',
            'data' => $kelas,
        ]);
    }

    /**
     * Update the specified kelas.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'kelas' => 'sometimes|string|max:10',
            'jurusan' => 'sometimes|string|max:50',
            'angkatan' => 'sometimes|integer|min:2000|max:2100',
            'wali_kelas' => 'nullable|exists:users,id',
            'kapasitas' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kelas->update($request->all());
        $kelas->load(['murids', 'waliKelas']);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil diupdate',
            'data' => [
                'id' => $kelas->id,
                'name' => $kelas->name,
                'kelas' => $kelas->kelas,
                'jurusan' => $kelas->jurusan,
                'angkatan' => $kelas->angkatan,
                'wali_kelas' => $kelas->waliKelas ? $kelas->waliKelas->name : null,
                'wali_kelas_id' => $kelas->wali_kelas,
                'kapasitas' => $kelas->kapasitas ?? 36,
                'jumlah_siswa' => $kelas->murids->count(),
            ],
        ]);
    }

    /**
     * Remove the specified kelas.
     */
    public function destroy($id): JsonResponse
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan',
            ], 404);
        }

        $kelas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dihapus',
        ]);
    }

    /**
     * Get kelas by jurusan.
     */
    public function byJurusan($jurusan): JsonResponse
    {
        $kelas = Kelas::where('jurusan', $jurusan)
            ->with('murids')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Data kelas jurusan {$jurusan} berhasil diambil",
            'data' => $kelas,
        ]);
    }

    /**
     * Get kelas by tingkat (X, XI, XII).
     */
    public function byTingkat($tingkat): JsonResponse
    {
        $kelas = Kelas::where('kelas', $tingkat)
            ->with('murids')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Data kelas tingkat {$tingkat} berhasil diambil",
            'data' => $kelas,
        ]);
    }
}
