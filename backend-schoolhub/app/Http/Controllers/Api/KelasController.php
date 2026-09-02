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
        $kelas = Kelas::with('murids')->get();

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kelas = Kelas::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $kelas,
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kelas->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil diupdate',
            'data' => $kelas,
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
