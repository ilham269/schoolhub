<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    /**
     * Display a listing of jadwal.
     */
    public function index(): JsonResponse
    {
        $jadwals = DB::table('jadwals')
            ->join('kelas', 'jadwals.kelas_id', '=', 'kelas.id')
            ->join('gurus', 'jadwals.guru_id', '=', 'gurus.id')
            ->join('mapels', 'jadwals.mapel_id', '=', 'mapels.id')
            ->select(
                'jadwals.*',
                'kelas.name as kelas_name',
                'gurus.nama_lengkap_guru as guru_name',
                'mapels.nama_mapel as mapel_name'
            )
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal berhasil diambil',
            'data' => $jadwals,
        ]);
    }

    /**
     * Store a newly created jadwal.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruangan' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $jadwal = DB::table('jadwals')->insertGetId([
            'kelas_id' => $request->kelas_id,
            'guru_id' => $request->guru_id,
            'mapel_id' => $request->mapel_id,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'ruangan' => $request->ruangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = DB::table('jadwals')->find($jadwal);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dibuat',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified jadwal.
     */
    public function show($id): JsonResponse
    {
        $jadwal = DB::table('jadwals')
            ->join('kelas', 'jadwals.kelas_id', '=', 'kelas.id')
            ->join('gurus', 'jadwals.guru_id', '=', 'gurus.id')
            ->join('mapels', 'jadwals.mapel_id', '=', 'mapels.id')
            ->where('jadwals.id', $id)
            ->select(
                'jadwals.*',
                'kelas.name as kelas_name',
                'gurus.nama_lengkap_guru as guru_name',
                'mapels.nama_mapel as mapel_name'
            )
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail jadwal berhasil diambil',
            'data' => $jadwal,
        ]);
    }

    /**
     * Update the specified jadwal.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $jadwal = DB::table('jadwals')->find($id);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kelas_id' => 'sometimes|exists:kelas,id',
            'guru_id' => 'sometimes|exists:gurus,id',
            'mapel_id' => 'sometimes|exists:mapels,id',
            'hari' => 'sometimes|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'sometimes|date_format:H:i',
            'jam_selesai' => 'sometimes|date_format:H:i',
            'ruangan' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('jadwals')->where('id', $id)->update(array_merge(
            $request->only(['kelas_id', 'guru_id', 'mapel_id', 'hari', 'jam_mulai', 'jam_selesai', 'ruangan']),
            ['updated_at' => now()]
        ));

        $data = DB::table('jadwals')->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diupdate',
            'data' => $data,
        ]);
    }

    /**
     * Remove the specified jadwal.
     */
    public function destroy($id): JsonResponse
    {
        $jadwal = DB::table('jadwals')->find($id);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        DB::table('jadwals')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus',
        ]);
    }

    /**
     * Get jadwal by kelas.
     */
    public function byKelas($kelasId): JsonResponse
    {
        $jadwals = DB::table('jadwals')
            ->join('gurus', 'jadwals.guru_id', '=', 'gurus.id')
            ->join('mapels', 'jadwals.mapel_id', '=', 'mapels.id')
            ->where('jadwals.kelas_id', $kelasId)
            ->select(
                'jadwals.*',
                'gurus.nama_lengkap_guru as guru_name',
                'mapels.nama_mapel as mapel_name'
            )
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berdasarkan kelas berhasil diambil',
            'data' => $jadwals,
        ]);
    }

    /**
     * Get jadwal by guru.
     */
    public function byGuru($guruId): JsonResponse
    {
        $jadwals = DB::table('jadwals')
            ->join('kelas', 'jadwals.kelas_id', '=', 'kelas.id')
            ->join('mapels', 'jadwals.mapel_id', '=', 'mapels.id')
            ->where('jadwals.guru_id', $guruId)
            ->select(
                'jadwals.*',
                'kelas.name as kelas_name',
                'mapels.nama_mapel as mapel_name'
            )
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berdasarkan guru berhasil diambil',
            'data' => $jadwals,
        ]);
    }

    /**
     * Get jadwal by hari.
     */
    public function byHari($hari): JsonResponse
    {
        $jadwals = DB::table('jadwals')
            ->join('kelas', 'jadwals.kelas_id', '=', 'kelas.id')
            ->join('gurus', 'jadwals.guru_id', '=', 'gurus.id')
            ->join('mapels', 'jadwals.mapel_id', '=', 'mapels.id')
            ->where('jadwals.hari', $hari)
            ->select(
                'jadwals.*',
                'kelas.name as kelas_name',
                'gurus.nama_lengkap_guru as guru_name',
                'mapels.nama_mapel as mapel_name'
            )
            ->orderBy('jam_mulai')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Jadwal hari {$hari} berhasil diambil",
            'data' => $jadwals,
        ]);
    }
}
