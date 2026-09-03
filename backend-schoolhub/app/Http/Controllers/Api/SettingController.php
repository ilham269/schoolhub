<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Get all settings.
     */
    public function index(): JsonResponse
    {
        // Simulasi data settings
        $settings = [
            'app' => [
                'nama_sekolah' => 'SMK Negeri 1 Jakarta',
                'alamat' => 'Jl. Pendidikan No. 1, Jakarta',
                'telepon' => '021-12345678',
                'email' => 'info@smkn1jakarta.sch.id',
                'website' => 'https://smkn1jakarta.sch.id',
                'logo' => '/images/logo.png',
                'favicon' => '/images/favicon.ico',
            ],
            'akademik' => [
                'tahun_ajaran' => '2026/2027',
                'semester' => 'Ganjil',
                'jumlah_jam_pelajaran' => 45, // dalam menit
                'waktu_mulai' => '07:00',
                'waktu_selesai' => '15:30',
            ],
            'sistem' => [
                'maintenance_mode' => false,
                'timezone' => 'Asia/Jakarta',
                'language' => 'id',
                'date_format' => 'd-m-Y',
                'time_format' => 'H:i',
            ],
            'email' => [
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_username' => 'noreply@smkn1jakarta.sch.id',
                'smtp_encryption' => 'tls',
            ],
            'notification' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data settings berhasil diambil',
            'data' => $settings,
        ]);
    }

    /**
     * Get setting by key.
     */
    public function show($key): JsonResponse
    {
        $settings = [
            'app' => [
                'nama_sekolah' => 'SMK Negeri 1 Jakarta',
                'alamat' => 'Jl. Pendidikan No. 1, Jakarta',
                'telepon' => '021-12345678',
                'email' => 'info@smkn1jakarta.sch.id',
            ],
            'akademik' => [
                'tahun_ajaran' => '2026/2027',
                'semester' => 'Ganjil',
            ],
            'sistem' => [
                'maintenance_mode' => false,
                'timezone' => 'Asia/Jakarta',
            ],
        ];

        $data = $settings[$key] ?? null;

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Setting tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Setting {$key} berhasil diambil",
            'data' => $data,
        ]);
    }

    /**
     * Update app settings.
     */
    public function updateApp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_sekolah' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string',
            'telepon' => 'sometimes|string|max:20',
            'email' => 'sometimes|email',
            'website' => 'sometimes|url',
            'logo' => 'sometimes|string',
            'favicon' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi update
        $settings = [
            'nama_sekolah' => $request->nama_sekolah ?? 'SMK Negeri 1 Jakarta',
            'alamat' => $request->alamat ?? 'Jl. Pendidikan No. 1, Jakarta',
            'telepon' => $request->telepon ?? '021-12345678',
            'email' => $request->email ?? 'info@smkn1jakarta.sch.id',
            'website' => $request->website ?? 'https://smkn1jakarta.sch.id',
            'logo' => $request->logo ?? '/images/logo.png',
            'favicon' => $request->favicon ?? '/images/favicon.ico',
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Setting aplikasi berhasil diupdate',
            'data' => $settings,
        ]);
    }

    /**
     * Update akademik settings.
     */
    public function updateAkademik(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'sometimes|string|max:20',
            'semester' => 'sometimes|in:Ganjil,Genap',
            'jumlah_jam_pelajaran' => 'sometimes|integer|min:30|max:60',
            'waktu_mulai' => 'sometimes|date_format:H:i',
            'waktu_selesai' => 'sometimes|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi update
        $settings = [
            'tahun_ajaran' => $request->tahun_ajaran ?? '2026/2027',
            'semester' => $request->semester ?? 'Ganjil',
            'jumlah_jam_pelajaran' => $request->jumlah_jam_pelajaran ?? 45,
            'waktu_mulai' => $request->waktu_mulai ?? '07:00',
            'waktu_selesai' => $request->waktu_selesai ?? '15:30',
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Setting akademik berhasil diupdate',
            'data' => $settings,
        ]);
    }

    /**
     * Update sistem settings.
     */
    public function updateSistem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'maintenance_mode' => 'sometimes|boolean',
            'timezone' => 'sometimes|string',
            'language' => 'sometimes|in:id,en',
            'date_format' => 'sometimes|string',
            'time_format' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi update
        $settings = [
            'maintenance_mode' => $request->maintenance_mode ?? false,
            'timezone' => $request->timezone ?? 'Asia/Jakarta',
            'language' => $request->language ?? 'id',
            'date_format' => $request->date_format ?? 'd-m-Y',
            'time_format' => $request->time_format ?? 'H:i',
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Setting sistem berhasil diupdate',
            'data' => $settings,
        ]);
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'smtp_host' => 'sometimes|string',
            'smtp_port' => 'sometimes|integer',
            'smtp_username' => 'sometimes|email',
            'smtp_password' => 'sometimes|string',
            'smtp_encryption' => 'sometimes|in:tls,ssl',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi update (password tidak ditampilkan)
        $settings = [
            'smtp_host' => $request->smtp_host ?? 'smtp.gmail.com',
            'smtp_port' => $request->smtp_port ?? 587,
            'smtp_username' => $request->smtp_username ?? 'noreply@smkn1jakarta.sch.id',
            'smtp_encryption' => $request->smtp_encryption ?? 'tls',
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Setting email berhasil diupdate',
            'data' => $settings,
        ]);
    }

    /**
     * Update notification settings.
     */
    public function updateNotification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_enabled' => 'sometimes|boolean',
            'sms_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulasi update
        $settings = [
            'email_enabled' => $request->email_enabled ?? true,
            'sms_enabled' => $request->sms_enabled ?? false,
            'push_enabled' => $request->push_enabled ?? true,
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Setting notifikasi berhasil diupdate',
            'data' => $settings,
        ]);
    }

    /**
     * Reset settings to default.
     */
    public function reset($key): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Setting {$key} berhasil direset ke default",
        ]);
    }
}
