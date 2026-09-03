<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{
    /**
     * Display a listing of pengumuman.
     */
    public function index(): JsonResponse
    {
        // Simulasi data pengumuman (bisa diganti dengan query database nanti)
        $pengumumans = [
            [
                'id' => 1,
                'judul' => 'Libur Semester Ganjil 2026',
                'konten' => 'Libur semester ganjil akan dimulai tanggal 15 Desember 2026',
                'tanggal' => '2026-12-01',
                'kategori' => 'Akademik',
                'prioritas' => 'Tinggi',
                'is_published' => true,
                'created_by' => 'Admin',
            ],
            [
                'id' => 2,
                'judul' => 'Pendaftaran Ekstrakurikuler',
                'konten' => 'Pendaftaran ekstrakurikuler dibuka mulai hari ini',
                'tanggal' => '2026-09-01',
                'kategori' => 'Kegiatan',
                'prioritas' => 'Sedang',
                'is_published' => true,
                'created_by' => 'Admin',
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data pengumuman berhasil diambil',
            'data' => $pengumumans,
        ]);
    }

    /**
     * Store a newly created pengumuman.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tanggal' => 'required|date',
            'kategori' => 'required|in:Akademik,Kegiatan,Administrasi,Umum',
            'prioritas' => 'required|in:Tinggi,Sedang,Rendah',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi create (implementasi database nanti)
        $pengumuman = [
            'id' => rand(100, 999),
            'judul' => $request->judul,
            'konten' => $request->konten,
            'tanggal' => $request->tanggal,
            'kategori' => $request->kategori,
            'prioritas' => $request->prioritas,
            'is_published' => $request->is_published ?? true,
            'created_by' => auth()->user()->name ?? 'Admin',
            'created_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dibuat',
            'data' => $pengumuman,
        ], 201);
    }

    /**
     * Display the specified pengumuman.
     */
    public function show($id): JsonResponse
    {
        // Simulasi find by id
        $pengumuman = [
            'id' => $id,
            'judul' => 'Libur Semester Ganjil 2026',
            'konten' => 'Libur semester ganjil akan dimulai tanggal 15 Desember 2026. Semua siswa diharapkan mengumpulkan tugas sebelum tanggal tersebut.',
            'tanggal' => '2026-12-01',
            'kategori' => 'Akademik',
            'prioritas' => 'Tinggi',
            'is_published' => true,
            'created_by' => 'Admin',
            'created_at' => '2026-12-01 10:00:00',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail pengumuman berhasil diambil',
            'data' => $pengumuman,
        ]);
    }

    /**
     * Update the specified pengumuman.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|string|max:255',
            'konten' => 'sometimes|string',
            'tanggal' => 'sometimes|date',
            'kategori' => 'sometimes|in:Akademik,Kegiatan,Administrasi,Umum',
            'prioritas' => 'sometimes|in:Tinggi,Sedang,Rendah',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi update
        $pengumuman = [
            'id' => $id,
            'judul' => $request->judul ?? 'Libur Semester Ganjil 2026',
            'konten' => $request->konten ?? 'Updated content',
            'tanggal' => $request->tanggal ?? '2026-12-01',
            'kategori' => $request->kategori ?? 'Akademik',
            'prioritas' => $request->prioritas ?? 'Tinggi',
            'is_published' => $request->is_published ?? true,
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diupdate',
            'data' => $pengumuman,
        ]);
    }

    /**
     * Remove the specified pengumuman.
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus',
        ]);
    }

    /**
     * Get pengumuman by kategori.
     */
    public function byKategori($kategori): JsonResponse
    {
        $pengumumans = [
            [
                'id' => 1,
                'judul' => 'Libur Semester Ganjil 2026',
                'konten' => 'Libur semester ganjil akan dimulai tanggal 15 Desember 2026',
                'tanggal' => '2026-12-01',
                'kategori' => $kategori,
                'prioritas' => 'Tinggi',
                'is_published' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => "Data pengumuman kategori {$kategori} berhasil diambil",
            'data' => $pengumumans,
        ]);
    }

    /**
     * Get published pengumuman only.
     */
    public function published(): JsonResponse
    {
        $pengumumans = [
            [
                'id' => 1,
                'judul' => 'Libur Semester Ganjil 2026',
                'konten' => 'Libur semester ganjil akan dimulai tanggal 15 Desember 2026',
                'tanggal' => '2026-12-01',
                'kategori' => 'Akademik',
                'prioritas' => 'Tinggi',
                'is_published' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data pengumuman yang dipublikasi berhasil diambil',
            'data' => $pengumumans,
        ]);
    }
}
