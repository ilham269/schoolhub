<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Murid;
use App\Models\News;
use Illuminate\Http\JsonResponse;

class PublicHomeController extends Controller
{
    public function home(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [
            'school' => [
                'name' => 'SMA Harapan Bangsa',
                'hero_title' => 'Memimpin Jalan Menuju Pendidikan Tinggi Berkualitas',
                'hero_description' => 'Tempat keunggulan akademik bertemu dengan pembentukan karakter.',
            ],
            'statistics' => [
                'students' => Murid::count(),
                'teachers' => Guru::count(),
                'ptn_percentage' => 0,
            ],
            'teachers' => $this->teachers(),
            'programs' => $this->programList(),
            'news' => News::query()->where('is_published', true)->latest('published_at')->limit(3)->get(),
            'ppdb' => ['year' => now()->year . '/' . (now()->year + 1), 'is_open' => true],
        ]]);
    }

    public function guru(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->teachers()]);
    }

    public function programs(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->programList()]);
    }

    private function teachers(): array
    {
        return Guru::with('user')->orderBy('id')->limit(4)->get()->map(fn (Guru $guru) => [
            'id' => $guru->id,
            'nama' => $guru->user?->name,
            'nama_lengkap_guru' => $guru->nama_lengkap_guru ?? $guru->user?->name,
            'gambar_guru' => $guru->gambar_guru,
        ])->all();
    }

    private function programList(): array
    {
        return [
            ['id' => 'mipa', 'nama' => 'Matematika dan Ilmu Pengetahuan Alam', 'kategori' => 'MIPA', 'deskripsi' => 'Program untuk mendalami sains, matematika, dan teknologi.'],
            ['id' => 'ips', 'nama' => 'Ilmu Pengetahuan Sosial', 'kategori' => 'IPS', 'deskripsi' => 'Program untuk memahami ekonomi, sosial, dan dinamika masyarakat.'],
            ['id' => 'bahasa', 'nama' => 'Bahasa dan Budaya', 'kategori' => 'Bahasa', 'deskripsi' => 'Program untuk mengembangkan literasi, komunikasi, dan wawasan budaya.'],
        ];
    }
}
