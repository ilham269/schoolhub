<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Murid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MuridController extends Controller
{
    /**
     * Display a listing of murid.
     */
    public function index(): JsonResponse
    {
        $murids = Murid::with(['user', 'kelas'])->get()->map(function (Murid $murid) {
            $data = $murid->toArray();
            $nama = $murid->Nama_lengkap_murid ?? $murid->user?->name ?? null;
            $data['Nama_lengkap_murid'] = $nama;
            $data['nama_lengkap_murid'] = $nama;
            return $data;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data murid berhasil diambil',
            'data' => $murids,
        ]);
    }

    /**
     * Import murid from CSV file.
     */
    public function import(Request $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'File CSV atau Excel wajib diupload',
                'errors' => ['file' => ['File CSV atau Excel wajib diupload']],
            ], 422);
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');
        $supportedExtensions = ['csv', 'txt', 'xls', 'xlsx', 'xlsm'];

        if (!in_array($extension, $supportedExtensions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Format file harus CSV atau Excel (.xls/.xlsx)',
                'errors' => ['file' => ['Format file harus CSV atau Excel (.xls/.xlsx)']],
            ], 422);
        }

        try {
            $rows = $this->readSpreadsheetRows($file);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file spreadsheet',
                'error' => $e->getMessage(),
            ], 422);
        }

        if (count($rows) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'File spreadsheet tidak memiliki data',
            ], 422);
        }

        $header = array_values(array_filter($rows[0], function ($column) {
            return $column !== null && trim((string) $column) !== '';
        }));

        if ($header === [] || count($header) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'File spreadsheet tidak memiliki header yang valid',
            ], 422);
        }

        $normalizedHeaders = array_map(function ($column) {
            return strtolower(trim(preg_replace('/\s+/', '_', (string) $column)));
        }, $header);

        $requiredHeaders = ['email', 'password', 'nis', 'nama_lengkap_murid', 'gender', 'kelas_id', 'tanggal_lahir'];
        $missing = array_values(array_diff($requiredHeaders, $normalizedHeaders));

        if ($missing) {
            return response()->json([
                'success' => false,
                'message' => 'Header spreadsheet tidak lengkap',
                'errors' => ['file' => ['Kolom yang dibutuhkan: ' . implode(', ', $requiredHeaders)]],
                'missing_headers' => $missing,
            ], 422);
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            if (count(array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rowData = [];
            foreach ($normalizedHeaders as $headerIndex => $headerName) {
                $rowData[$headerName] = $row[$headerIndex] ?? null;
            }

            $validator = Validator::make($rowData, [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'nis' => 'required|string|unique:murids,nis',
                'nama_lengkap_murid' => 'required|string|max:255',
                'gender' => 'required|in:L,P',
                'kelas_id' => 'required|exists:kelas,id',
                'tanggal_lahir' => 'required|date',
                'tempat_lahir' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'nomor_telepon' => 'nullable|string|max:20',
                'nama_ayah' => 'nullable|string|max:255',
                'nama_ibu' => 'nullable|string|max:255',
                'pekerjaan_ayah' => 'nullable|string|max:255',
                'pekerjaan_ibu' => 'nullable|string|max:255',
                'nomor_telepon_ortu' => 'nullable|string|max:20',
                'agama' => 'nullable|string|max:50',
                'anak_ke' => 'nullable|integer|min:1',
                'jumlah_saudara' => 'nullable|integer|min:0',
                'hobi' => 'nullable|string|max:255',
                'cita_cita' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = [
                    'row' => $index + 2,
                    'message' => $validator->errors()->first(),
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                $user = User::create([
                    'name' => $rowData['nama_lengkap_murid'],
                    'email' => $rowData['email'],
                    'password' => Hash::make($rowData['password']),
                    'role' => 'Murid',
                    'is_active' => true,
                ]);

                Murid::create([
                    'user_id' => $user->id,
                    'kelas_id' => $rowData['kelas_id'],
                    'nis' => $rowData['nis'],
                    'Nama_lengkap_murid' => $rowData['nama_lengkap_murid'],
                    'gender' => $rowData['gender'],
                    'tanggal_lahir' => $rowData['tanggal_lahir'],
                    'tempat_lahir' => $rowData['tempat_lahir'] ?? null,
                    'alamat' => $rowData['alamat'] ?? null,
                    'nomor_telepon' => $rowData['nomor_telepon'] ?? null,
                    'nama_ayah' => $rowData['nama_ayah'] ?? null,
                    'nama_ibu' => $rowData['nama_ibu'] ?? null,
                    'pekerjaan_ayah' => $rowData['pekerjaan_ayah'] ?? null,
                    'pekerjaan_ibu' => $rowData['pekerjaan_ibu'] ?? null,
                    'nomor_telepon_ortu' => $rowData['nomor_telepon_ortu'] ?? null,
                    'agama' => $rowData['agama'] ?? null,
                    'anak_ke' => $rowData['anak_ke'] ?? null,
                    'jumlah_saudara' => $rowData['jumlah_saudara'] ?? null,
                    'hobi' => $rowData['hobi'] ?? null,
                    'cita_cita' => $rowData['cita_cita'] ?? null,
                ]);

                DB::commit();
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $errors[] = [
                    'row' => $index + 2,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Import data murid selesai',
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    private function readSpreadsheetRows($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');

        if (in_array($extension, ['csv', 'txt'], true)) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                throw new \RuntimeException('Gagal membuka file CSV');
            }

            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);

            return $rows;
        }

        if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            throw new \RuntimeException('Library spreadsheet belum terinstal');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = [];

        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $values = [];

            foreach ($cellIterator as $cell) {
                $values[] = $cell->getValue();
            }

            $rows[] = $values;
        }

        return $rows;
    }

    /**
     * Store a newly created murid.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nis' => 'required|string|unique:murids,nis',
            'nama_lengkap_murid' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'nullable|string|max:255',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'nama_orangtua' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_telepon_ortu' => 'nullable|string|max:20',
            'agama' => 'nullable|string|max:50',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $request->nama_lengkap_murid,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Murid',
                'is_active' => true,
            ]);

            // Create murid profile
            $murid = Murid::create([
                'user_id' => $user->id,
                'kelas_id' => $request->kelas_id,
                'nis' => $request->nis,
                'Nama_lengkap_murid' => $request->nama_lengkap_murid,
                'gender' => $request->gender,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tempat_lahir' => $request->tempat_lahir,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
                'nama_orangtua' => $request->nama_orangtua,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'nomor_telepon_ortu' => $request->nomor_telepon_ortu,
                'agama' => $request->agama,
                'anak_ke' => $request->anak_ke,
                'jumlah_saudara' => $request->jumlah_saudara,
                'hobi' => $request->hobi,
                'cita_cita' => $request->cita_cita,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Murid berhasil ditambahkan',
                'data' => $murid->load(['user', 'kelas']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan murid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Profil murid yang sedang login.
     * PENTING: route ini harus didaftarkan SEBELUM /{id} di routes/api.php,
     * kalau tidak, 'profile' akan tertangkap sebagai nilai {id}.
     */
    public function myProfile(Request $request): JsonResponse
    {
        $murid = Murid::with('kelas')->where('user_id', $request->user()->id)->first();

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        // Kolom database memakai 'Nama_lengkap_murid' (N besar).
        // Disamakan ke 'nama_lengkap_murid' supaya cocok dengan frontend.
        $data = $murid->toArray();
        $data['nama_lengkap_murid'] = $murid->Nama_lengkap_murid;

        return response()->json([
            'success' => true,
            'message' => 'Profil murid berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Update profil murid yang sedang login.
     * Murid hanya boleh mengubah datanya sendiri, bukan nis/kelas_id.
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        $murid = Murid::where('user_id', $request->user()->id)->first();

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap_murid' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'agama' => 'nullable|string|max:50',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'nomor_telepon' => 'nullable|string|max:20',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'alamat' => 'nullable|string',
            'nama_orangtua' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_telepon_ortu' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Samakan lagi ke nama kolom asli sebelum disimpan.
        $validated['Nama_lengkap_murid'] = $validated['nama_lengkap_murid'];
        unset($validated['nama_lengkap_murid']);

        DB::beginTransaction();
        try {
            $murid->update($validated);

            // Nama di tabel users ikut disamakan (opsional, biar konsisten).
            $murid->user?->update(['name' => $validated['Nama_lengkap_murid']]);

            DB::commit();

            $murid->refresh()->load('kelas');
            $data = $murid->toArray();
            $data['nama_lengkap_murid'] = $murid->Nama_lengkap_murid;

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified murid.
     */
    public function show($id): JsonResponse
    {
        $murid = Murid::with(['user', 'kelas'])->find($id);

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        $data = $murid->toArray();
        $nama = $murid->Nama_lengkap_murid ?? $murid->user?->name ?? null;
        $data['Nama_lengkap_murid'] = $nama;
        $data['nama_lengkap_murid'] = $nama;

        return response()->json([
            'success' => true,
            'message' => 'Detail murid berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Update the specified murid.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $murid = Murid::find($id);

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,' . $murid->user_id,
            'password' => 'sometimes|string|min:8',
            'nis' => 'sometimes|string|unique:murids,nis,' . $murid->id,
            'nama_lengkap_murid' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:L,P',
            'kelas_id' => 'sometimes|exists:kelas,id',
            'tanggal_lahir' => 'sometimes|date',
            'tempat_lahir' => 'nullable|string|max:255',
            'alamat' => 'sometimes|string',
            'nomor_telepon' => 'sometimes|string|max:20',
            'nama_orangtua' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_telepon_ortu' => 'nullable|string|max:20',
            'agama' => 'nullable|string|max:50',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update user
            $userData = [];
            if ($request->has('nama_lengkap_murid')) $userData['name'] = $request->nama_lengkap_murid;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->filled('password')) $userData['password'] = Hash::make($request->password);

            if (!empty($userData)) {
                $murid->user->update($userData);
            }

            // Update murid profile
            $muridData = $request->only([
                'kelas_id',
                'nis',
                'nama_lengkap_murid',
                'gender',
                'tanggal_lahir',
                'tempat_lahir',
                'alamat',
                'nomor_telepon',
                'nama_orangtua',
                'nama_ayah',
                'nama_ibu',
                'pekerjaan_ayah',
                'pekerjaan_ibu',
                'nomor_telepon_ortu',
                'agama',
                'anak_ke',
                'jumlah_saudara',
                'hobi',
                'cita_cita',
            ]);

            // Use Nama_lengkap_murid (with capital N) for database field
            if (isset($muridData['nama_lengkap_murid'])) {
                $muridData['Nama_lengkap_murid'] = $muridData['nama_lengkap_murid'];
                unset($muridData['nama_lengkap_murid']);
            }

            $murid->update($muridData);

            DB::commit();

            $murid->refresh()->load(['user', 'kelas']);
            $data = $murid->toArray();
            $data['nama_lengkap_murid'] = $murid->Nama_lengkap_murid ?? $murid->user?->name ?? null;

            return response()->json([
                'success' => true,
                'message' => 'Murid berhasil diupdate',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate murid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified murid.
     */
    public function destroy($id): JsonResponse
    {
        $murid = Murid::find($id);

        if (!$murid) {
            return response()->json([
                'success' => false,
                'message' => 'Murid tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $murid->user->delete(); // Will cascade delete murid
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Murid berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus murid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get murids by kelas.
     */
    public function byKelas($kelasId): JsonResponse
    {
        $murids = Murid::where('kelas_id', $kelasId)
            ->with(['user', 'kelas'])
            ->get();

        $murids = $murids->map(function (Murid $murid) {
            $data = $murid->toArray();
            $data['nama_lengkap_murid'] = $murid->Nama_lengkap_murid ?? $murid->user?->name ?? null;
            return $data;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data murid berdasarkan kelas berhasil diambil',
            'data' => $murids,
        ]);
    }
}
