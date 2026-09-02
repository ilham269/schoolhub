<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Murid;
use App\Models\Karyawan;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'admin' => User::where('role', 'Admin')->count(),
                'guru' => User::where('role', 'Guru')->count(),
                'murid' => User::where('role', 'Murid')->count(),
                'karyawan' => User::where('role', 'Karyawan')->count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
            'kelas' => [
                'total' => Kelas::count(),
                'x' => Kelas::where('kelas', 'X')->count(),
                'xi' => Kelas::where('kelas', 'XI')->count(),
                'xii' => Kelas::where('kelas', 'XII')->count(),
                'rpl' => Kelas::where('jurusan', 'RPL')->count(),
                'tkr' => Kelas::where('jurusan', 'TKR')->count(),
                'tsm' => Kelas::where('jurusan', 'TSM')->count(),
            ],
            'akademik' => [
                'total_guru' => Guru::count(),
                'total_murid' => Murid::count(),
                'total_karyawan' => Karyawan::count(),
                'total_mapel' => 0, // akan diisi dari database
                'total_jadwal' => 0, // akan diisi dari database
                'total_materi' => 0, // akan diisi dari database
                'total_tugas' => 0, // akan diisi dari database
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => $stats,
        ]);
    }

    /**
     * Get dashboard for Admin.
     */
    public function admin(): JsonResponse
    {
        $data = [
            'summary' => [
                'total_users' => User::count(),
                'total_guru' => Guru::count(),
                'total_murid' => Murid::count(),
                'total_karyawan' => Karyawan::count(),
                'total_kelas' => Kelas::count(),
            ],
            'recent_activities' => [
                ['action' => 'User baru terdaftar', 'user' => 'Ahmad Rizki', 'time' => '5 menit lalu'],
                ['action' => 'Guru menambah materi', 'user' => 'Dewi Sartika', 'time' => '10 menit lalu'],
                ['action' => 'Murid mengumpulkan tugas', 'user' => 'Budi Setiawan', 'time' => '15 menit lalu'],
            ],
            'charts' => [
                'user_growth' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    'data' => [10, 15, 20, 25, 30, 35],
                ],
                'kelas_distribution' => [
                    'labels' => ['X RPL', 'XI RPL', 'XII RPL', 'X TKR', 'XI TKR'],
                    'data' => [30, 28, 25, 32, 30],
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Admin berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Get dashboard for Guru.
     */
    public function guru(): JsonResponse
    {
        $data = [
            'summary' => [
                'total_kelas' => 3,
                'total_murid' => 90,
                'total_materi' => 15,
                'total_tugas' => 8,
                'tugas_belum_dinilai' => 12,
            ],
            'jadwal_hari_ini' => [
                ['kelas' => 'XI RPL 1', 'mapel' => 'Pemrograman Web', 'jam' => '07:00 - 08:30', 'ruang' => 'Lab Komputer 1'],
                ['kelas' => 'XI RPL 2', 'mapel' => 'Basis Data', 'jam' => '09:00 - 10:30', 'ruang' => 'Lab Komputer 2'],
                ['kelas' => 'XII RPL 1', 'mapel' => 'Pemrograman Mobile', 'jam' => '13:00 - 14:30', 'ruang' => 'Lab Komputer 1'],
            ],
            'tugas_terbaru' => [
                ['judul' => 'Membuat CRUD Laravel', 'kelas' => 'XI RPL 1', 'deadline' => '2026-09-10', 'terkumpul' => 15, 'total' => 30],
                ['judul' => 'Database Normalisasi', 'kelas' => 'XI RPL 2', 'deadline' => '2026-09-12', 'terkumpul' => 10, 'total' => 28],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Guru berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Get dashboard for Murid.
     */
    public function murid(): JsonResponse
    {
        $data = [
            'summary' => [
                'kelas' => 'XI RPL 1',
                'total_mapel' => 12,
                'total_tugas' => 8,
                'tugas_selesai' => 5,
                'tugas_pending' => 3,
                'nilai_rata_rata' => 85.5,
            ],
            'jadwal_hari_ini' => [
                ['mapel' => 'Pemrograman Web', 'guru' => 'Dewi Sartika, S.Pd', 'jam' => '07:00 - 08:30', 'ruang' => 'Lab Komputer 1'],
                ['mapel' => 'Basis Data', 'guru' => 'Ahmad Fauzi, S.Pd', 'jam' => '09:00 - 10:30', 'ruang' => 'Lab Komputer 2'],
                ['mapel' => 'Matematika', 'guru' => 'Budi Santoso, S.Pd', 'jam' => '10:30 - 12:00', 'ruang' => 'Ruang 101'],
            ],
            'tugas_pending' => [
                ['judul' => 'Membuat CRUD Laravel', 'mapel' => 'Pemrograman Web', 'deadline' => '2026-09-10', 'status' => 'Belum Dikerjakan'],
                ['judul' => 'Database Normalisasi', 'mapel' => 'Basis Data', 'deadline' => '2026-09-12', 'status' => 'Sedang Dikerjakan'],
                ['judul' => 'Soal Kalkulus', 'mapel' => 'Matematika', 'deadline' => '2026-09-15', 'status' => 'Belum Dikerjakan'],
            ],
            'nilai_terbaru' => [
                ['mapel' => 'Pemrograman Web', 'tugas' => 'Membuat Blog', 'nilai' => 90, 'tanggal' => '2026-09-01'],
                ['mapel' => 'Basis Data', 'tugas' => 'ERD Sistem Sekolah', 'nilai' => 85, 'tanggal' => '2026-08-30'],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Murid berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Get dashboard for Karyawan.
     */
    public function karyawan(): JsonResponse
    {
        $data = [
            'summary' => [
                'bagian' => 'Tata Usaha',
                'tugas_hari_ini' => 5,
                'tugas_selesai' => 3,
                'tugas_pending' => 2,
            ],
            'tugas_hari_ini' => [
                ['tugas' => 'Verifikasi dokumen siswa baru', 'prioritas' => 'Tinggi', 'status' => 'Selesai'],
                ['tugas' => 'Input data absensi guru', 'prioritas' => 'Sedang', 'status' => 'Sedang Dikerjakan'],
                ['tugas' => 'Arsip surat masuk', 'prioritas' => 'Rendah', 'status' => 'Pending'],
            ],
            'notifikasi' => [
                ['pesan' => 'Ada 5 dokumen baru yang perlu diverifikasi', 'waktu' => '10 menit lalu'],
                ['pesan' => 'Surat edaran baru dari Kepala Sekolah', 'waktu' => '1 jam lalu'],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Karyawan berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Get report statistics.
     */
    public function report(): JsonResponse
    {
        $data = [
            'periode' => [
                'tahun_ajaran' => '2026/2027',
                'semester' => 'Ganjil',
            ],
            'akademik' => [
                'rata_rata_nilai' => 82.5,
                'kehadiran_murid' => 95.2,
                'kehadiran_guru' => 98.5,
                'total_materi' => 150,
                'total_tugas' => 85,
                'tugas_terkumpul' => 1500,
                'tugas_total' => 1700,
            ],
            'prestasi' => [
                'juara_1' => 5,
                'juara_2' => 8,
                'juara_3' => 10,
            ],
            'charts' => [
                'nilai_per_mapel' => [
                    'labels' => ['Pemrograman Web', 'Basis Data', 'Mobile', 'Desktop', 'Matematika'],
                    'data' => [85, 80, 88, 82, 75],
                ],
                'kehadiran_bulanan' => [
                    'labels' => ['Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                    'data' => [95, 94, 96, 95, 97, 96],
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data laporan berhasil diambil',
            'data' => $data,
        ]);
    }
}
