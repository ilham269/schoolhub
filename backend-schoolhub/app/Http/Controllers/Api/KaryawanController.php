<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /**
     * Display a listing of karyawan.
     */
    public function index(): JsonResponse
    {
        $karyawans = Karyawan::with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diambil',
            'data' => $karyawans,
        ]);
    }

    /**
     * Import karyawan from CSV file.
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

        $requiredHeaders = ['email', 'password', 'nip', 'nama_lengkap_karyawan', 'bagian', 'nomor_telepon', 'alamat'];
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
                'nip' => 'required|string|unique:karyawans,nip',
                'nama_lengkap_karyawan' => 'required|string|max:255',
                'bagian' => 'required|string|max:100',
                'nomor_telepon' => 'required|string|max:20',
                'alamat' => 'required|string',
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
                    'name' => $rowData['nama_lengkap_karyawan'],
                    'email' => $rowData['email'],
                    'password' => Hash::make($rowData['password']),
                    'role' => 'Karyawan',
                    'is_active' => true,
                ]);

                Karyawan::create([
                    'user_id' => $user->id,
                    'nip' => $rowData['nip'],
                    'nama_lengkap_karyawan' => $rowData['nama_lengkap_karyawan'],
                    'bagian' => $rowData['bagian'],
                    'nomor_telepon' => $rowData['nomor_telepon'],
                    'alamat' => $rowData['alamat'],
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
            'message' => 'Import data karyawan selesai',
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
     * Store a newly created karyawan.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nip' => 'required|string|unique:karyawans,nip',
            'nama_lengkap_karyawan' => 'required|string|max:255',
            'bagian' => 'required|string|max:100',
            'nomor_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'tanggal_lahir' => 'nullable|date',
            'gender' => 'nullable|in:L,P',
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
                'name' => $request->nama_lengkap_karyawan,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Karyawan',
                'is_active' => true,
            ]);

            // Create karyawan profile
            $karyawan = Karyawan::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_lengkap_karyawan' => $request->nama_lengkap_karyawan,
                'bagian' => $request->bagian,
                'nomor_telepon' => $request->nomor_telepon,
                'alamat' => $request->alamat,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan',
                'data' => $karyawan->load('user'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan karyawan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified karyawan.
     */
    public function show($id): JsonResponse
    {
        $karyawan = Karyawan::with('user')->find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil',
            'data' => $karyawan,
        ]);
    }

    /**
     * Update the specified karyawan.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $karyawan = Karyawan::find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,' . $karyawan->user_id,
            'password' => 'sometimes|string|min:8',
            'nip' => 'sometimes|string|unique:karyawans,nip,' . $karyawan->id,
            'nama_lengkap_karyawan' => 'sometimes|string|max:255',
            'bagian' => 'sometimes|string|max:100',
            'nomor_telepon' => 'sometimes|string|max:20',
            'alamat' => 'sometimes|string',
            'tanggal_lahir' => 'nullable|date',
            'gender' => 'nullable|in:L,P',
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
            if ($request->has('nama_lengkap_karyawan')) $userData['name'] = $request->nama_lengkap_karyawan;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->filled('password')) $userData['password'] = Hash::make($request->password);

            if (!empty($userData)) {
                $karyawan->user->update($userData);
            }

            // Update karyawan profile
            $karyawanData = $request->only([
                'nip',
                'nama_lengkap_karyawan',
                'bagian',
                'nomor_telepon',
                'alamat',
            ]);

            $karyawan->update($karyawanData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diupdate',
                'data' => $karyawan->load('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate karyawan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified karyawan.
     */
    public function destroy($id): JsonResponse
    {
        $karyawan = Karyawan::find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $karyawan->user->delete(); // Will cascade delete karyawan
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get karyawans by bagian.
     */
    public function byBagian($bagian): JsonResponse
    {
        $karyawans = Karyawan::where('bagian', $bagian)
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Data karyawan bagian {$bagian} berhasil diambil",
            'data' => $karyawans,
        ]);
    }
}
