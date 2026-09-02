<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BeritaController extends Controller
{
    /**
     * Display a listing of berita.
     */
    public function index(): JsonResponse
    {
        // Simulasi data berita
        $beritas = [
            [
                'id' => 1,
                'judul' => 'Siswa SMK Raih Juara Lomba Programming',
                'slug' => 'siswa-smk-raih-juara-lomba-programming',
                'konten' => 'Siswa kelas XI RPL berhasil meraih juara 1 dalam lomba programming tingkat nasional',
                'excerpt' => 'Siswa kelas XI RPL berhasil meraih juara 1 dalam lomba programming',
                'gambar' => '/images/berita1.jpg',
                'tanggal' => '2026-08-25',
                'kategori' => 'Prestasi',
                'penulis' => 'Admin',
                'views' => 150,
                'is_published' => true,
            ],
            [
                'id' => 2,
                'judul' => 'Workshop Teknologi AI untuk Siswa',
                'slug' => 'workshop-teknologi-ai-untuk-siswa',
                'konten' => 'Sekolah mengadakan workshop tentang teknologi AI untuk siswa kelas XII',
                'excerpt' => 'Sekolah mengadakan workshop tentang teknologi AI',
                'gambar' => '/images/berita2.jpg',
                'tanggal' => '2026-08-20',
                'kategori' => 'Kegiatan',
                'penulis' => 'Admin',
                'views' => 89,
                'is_published' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data berita berhasil diambil',
            'data' => $beritas,
        ]);
    }

    /**
     * Store a newly created berita.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:beritas,slug',
            'konten' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'gambar' => 'nullable|string',
            'tanggal' => 'required|date',
            'kategori' => 'required|in:Prestasi,Kegiatan,Akademik,Umum',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi create
        $berita = [
            'id' => rand(100, 999),
            'judul' => $request->judul,
            'slug' => $request->slug,
            'konten' => $request->konten,
            'excerpt' => $request->excerpt,
            'gambar' => $request->gambar,
            'tanggal' => $request->tanggal,
            'kategori' => $request->kategori,
            'penulis' => auth()->user()->name ?? 'Admin',
            'views' => 0,
            'is_published' => $request->is_published ?? true,
            'created_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dibuat',
            'data' => $berita,
        ], 201);
    }

    /**
     * Display the specified berita.
     */
    public function show($id): JsonResponse
    {
        // Simulasi find by id (increment views)
        $berita = [
            'id' => $id,
            'judul' => 'Siswa SMK Raih Juara Lomba Programming',
            'slug' => 'siswa-smk-raih-juara-lomba-programming',
            'konten' => 'Siswa kelas XI RPL berhasil meraih juara 1 dalam lomba programming tingkat nasional. Prestasi ini merupakan yang pertama kali diraih oleh sekolah.',
            'excerpt' => 'Siswa kelas XI RPL berhasil meraih juara 1 dalam lomba programming',
            'gambar' => '/images/berita1.jpg',
            'tanggal' => '2026-08-25',
            'kategori' => 'Prestasi',
            'penulis' => 'Admin',
            'views' => 151, // incremented
            'is_published' => true,
            'created_at' => '2026-08-25 10:00:00',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail berita berhasil diambil',
            'data' => $berita,
        ]);
    }

    /**
     * Display the specified berita by slug.
     */
    public function showBySlug($slug): JsonResponse
    {
        // Simulasi find by slug
        $berita = [
            'id' => 1,
            'judul' => 'Siswa SMK Raih Juara Lomba Programming',
            'slug' => $slug,
            'konten' => 'Siswa kelas XI RPL berhasil meraih juara 1 dalam lomba programming tingkat nasional.',
            'excerpt' => 'Siswa kelas XI RPL berhasil meraih juara 1 dalam lomba programming',
            'gambar' => '/images/berita1.jpg',
            'tanggal' => '2026-08-25',
            'kategori' => 'Prestasi',
            'penulis' => 'Admin',
            'views' => 151,
            'is_published' => true,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail berita berhasil diambil',
            'data' => $berita,
        ]);
    }

    /**
     * Update the specified berita.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'konten' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:500',
            'gambar' => 'nullable|string',
            'tanggal' => 'sometimes|date',
            'kategori' => 'sometimes|in:Prestasi,Kegiatan,Akademik,Umum',
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
        $berita = [
            'id' => $id,
            'judul' => $request->judul ?? 'Updated Title',
            'slug' => $request->slug ?? 'updated-slug',
            'konten' => $request->konten ?? 'Updated content',
            'excerpt' => $request->excerpt,
            'gambar' => $request->gambar,
            'tanggal' => $request->tanggal ?? '2026-08-25',
            'kategori' => $request->kategori ?? 'Prestasi',
            'is_published' => $request->is_published ?? true,
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diupdate',
            'data' => $berita,
        ]);
    }

    /**
     * Remove the specified berita.
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus',
        ]);
    }

    /**
     * Get berita by kategori.
     */
    public function byKategori($kategori): JsonResponse
    {
        $beritas = [
            [
                'id' => 1,
                'judul' => 'Siswa SMK Raih Juara Lomba Programming',
                'slug' => 'siswa-smk-raih-juara-lomba-programming',
                'excerpt' => 'Siswa kelas XI RPL berhasil meraih juara 1',
                'gambar' => '/images/berita1.jpg',
                'tanggal' => '2026-08-25',
                'kategori' => $kategori,
                'views' => 150,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => "Data berita kategori {$kategori} berhasil diambil",
            'data' => $beritas,
        ]);
    }

    /**
     * Get published berita only.
     */
    public function published(): JsonResponse
    {
        $beritas = [
            [
                'id' => 1,
                'judul' => 'Siswa SMK Raih Juara Lomba Programming',
                'slug' => 'siswa-smk-raih-juara-lomba-programming',
                'excerpt' => 'Siswa kelas XI RPL berhasil meraih juara 1',
                'gambar' => '/images/berita1.jpg',
                'tanggal' => '2026-08-25',
                'kategori' => 'Prestasi',
                'views' => 150,
                'is_published' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data berita yang dipublikasi berhasil diambil',
            'data' => $beritas,
        ]);
    }

    /**
     * Get latest berita.
     */
    public function latest($limit = 5): JsonResponse
    {
        $beritas = [
            [
                'id' => 1,
                'judul' => 'Siswa SMK Raih Juara Lomba Programming',
                'slug' => 'siswa-smk-raih-juara-lomba-programming',
                'excerpt' => 'Siswa kelas XI RPL berhasil meraih juara 1',
                'gambar' => '/images/berita1.jpg',
                'tanggal' => '2026-08-25',
                'kategori' => 'Prestasi',
                'views' => 150,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data berita terbaru berhasil diambil',
            'data' => array_slice($beritas, 0, $limit),
        ]);
    }
}
