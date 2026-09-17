<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    /**
     * Display a listing of guru.
     */
    public function index(): JsonResponse
    {
        $gurus = Guru::with('user')->get()->map(function ($guru) {
            return [
                'id' => $guru->id,
                'user_id' => $guru->user_id,
                'nip' => $guru->nip,
                'nama' => $guru->user->name,
                'nama_lengkap_guru' => $guru->nama_lengkap_guru,
                'email' => $guru->user->email,
                'gender' => $guru->gender,
                'tanggal_lahir' => $guru->tanggal_lahir,
                'alamat' => $guru->alamat,
                'nomor_telepon' => $guru->nomor_telepon,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil diambil',
            'data' => $gurus,
        ]);
    }

    /**
     * Import guru from CSV file.
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

        $requiredHeaders = ['email', 'password', 'nip', 'nama_lengkap_guru', 'gender', 'tanggal_lahir'];
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
                'nip' => 'required|string|unique:gurus,nip',
                'nama_lengkap_guru' => 'required|string|max:255',
                'gender' => 'required|in:L,P',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'nullable|string',
                'nomor_telepon' => 'nullable|string|max:20',
                'pendidikan_terakhir' => 'nullable|string|max:100',
                'mata_pelajaran' => 'nullable|string|max:255',
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
                    'name' => $rowData['nama_lengkap_guru'],
                    'email' => $rowData['email'],
                    'password' => Hash::make($rowData['password']),
                    'role' => 'Guru',
                    'is_active' => true,
                ]);

                Guru::create([
                    'user_id' => $user->id,
                    'nip' => $rowData['nip'],
                    'nama_lengkap_guru' => $rowData['nama_lengkap_guru'],
                    'gender' => $rowData['gender'],
                    'tanggal_lahir' => $rowData['tanggal_lahir'],
                    'alamat' => $rowData['alamat'] ?? null,
                    'nomor_telepon' => $rowData['nomor_telepon'] ?? null,
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
            'message' => 'Import data guru selesai',
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
     * Store a newly created guru.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nip' => 'required|string|unique:gurus,nip',
            'nama_lengkap_guru' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
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
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Guru',
                'is_active' => true,
            ]);

            // Create guru profile
            $guru = Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_lengkap_guru' => $request->nama_lengkap_guru,
                'gender' => $request->gender,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil ditambahkan',
                'data' => $guru->load('user'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified guru.
     */
    public function show($id): JsonResponse
    {
        $guru = Guru::with('user')->find($id);

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail guru berhasil diambil',
            'data' => $guru,
        ]);
    }

    /**
     * Update the specified guru.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $guru->user_id,
            'password' => 'sometimes|string|min:8',
            'nip' => 'sometimes|string|unique:gurus,nip,' . $guru->id,
            'nama_lengkap_guru' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:L,P',
            'tanggal_lahir' => 'sometimes|date',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
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
            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('password')) $userData['password'] = Hash::make($request->password);

            if (!empty($userData)) {
                $guru->user->update($userData);
            }

            // Update guru profile
            $guruData = $request->only([
                'nip',
                'nama_lengkap_guru',
                'gender',
                'tanggal_lahir',
                'alamat',
                'nomor_telepon',
            ]);

            $guru->update($guruData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil diupdate',
                'data' => $guru->load('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified guru.
     */
    public function destroy($id): JsonResponse
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $guru->user->delete(); // Will cascade delete guru
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
