<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $news = [
            ['title' => 'Siswa SMA Harapan Bangsa Raih Juara Olimpiade Sains', 'category' => 'Prestasi', 'content' => 'Siswa SMA Harapan Bangsa berhasil meraih prestasi pada Olimpiade Sains tingkat kota. Prestasi ini menjadi bukti semangat belajar dan pendampingan guru yang konsisten.', 'published_at' => now()->subDays(2)],
            ['title' => 'Workshop Teknologi AI untuk Siswa Kelas XII', 'category' => 'Kegiatan', 'content' => 'Sekolah mengadakan workshop teknologi kecerdasan buatan untuk memperluas wawasan siswa menghadapi dunia perguruan tinggi dan kerja.', 'published_at' => now()->subDays(5)],
            ['title' => 'Informasi Persiapan Penilaian Tengah Semester', 'category' => 'Akademik', 'content' => 'Seluruh siswa diminta mempersiapkan diri untuk Penilaian Tengah Semester sesuai jadwal yang telah dibagikan oleh wali kelas.', 'published_at' => now()->subDays(8)],
        ];

        foreach ($news as $item) {
            News::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [...$item, 'excerpt' => Str::limit($item['content'], 150), 'author' => 'Admin Sekolah', 'is_published' => true]
            );
        }
    }
}
