<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TugasController extends Controller
{
    /**
     * Ambil ID guru yang sedang login.
     * User (role=guru) → hasOne → Guru, jadi perlu ambil guru->id
     */
    protected function guruId(Request $request): int
    {
        $user = $request->user();
        
        if (!$user) {
            abort(401, 'User tidak terautentikasi.');
        }
        
        // Case-insensitive check untuk role
        $userRole = strtolower($user->role);
        if ($userRole !== 'guru') {
            abort(403, 'Hanya guru yang dapat mengakses resource ini.');
        }
        
        $guru = $user->guru;
        
        if (!$guru) {
            abort(404, 'Data guru tidak ditemukan untuk user ini. Pastikan user memiliki profil guru.');
        }
        
        return (int) $guru->id;
    }

    /**
     * GET /api/guru/tugas
     *
     * Mengambil semua tugas milik guru yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $tugas = Tugas::query()
            ->where('guru_id', $this->guruId($request))
            ->with([
                'kelas',
                'mapel',
                'materi',
            ])
            ->withCount('pengumpulan')
            ->orderBy('deadline')
            ->get()
            ->map(function (Tugas $item) {
                return $this->transform($item);
            });

        return response()->json([
            'data' => $tugas,
        ]);
    }

    /**
     * GET /api/guru/tugas/{tugas}
     */
    public function show(Request $request, Tugas $tugas): JsonResponse
    {
        $this->authorizeOwnership($request, $tugas);

        $tugas->load([
            'kelas',
            'mapel',
            'materi',
        ])->loadCount('pengumpulan');

        return response()->json([
            'data' => $this->transform($tugas),
        ]);
    }

    /**
     * POST /api/guru/tugas
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tugas tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Guru yang membuat tugas diambil dari user yang sedang login.
        $data['guru_id'] = $this->guruId($request);

        // Tanggal pembuatan tugas.
        $data['tanggal_dibuat'] = now();
        $data['nilai_maksimal'] = $data['nilai_maksimal'] ?? 100;
        $data['is_active'] = $data['is_active'] ?? true;

        // Upload file jika ada.
        if ($request->hasFile('file')) {
            $data['file_path'] = $request
                ->file('file')
                ->store('tugas', 'public');
        }

        $tugas = Tugas::create($data);

        $tugas->load([
            'kelas',
            'mapel',
            'materi',
        ])->loadCount('pengumpulan');

        return response()->json([
            'message' => 'Tugas berhasil dibuat.',
            'data' => $this->transform($tugas),
        ], 201);
    }

    /**
     * PUT/PATCH /api/guru/tugas/{tugas}
     */
    public function update(Request $request, Tugas $tugas): JsonResponse
    {
        $this->authorizeOwnership($request, $tugas);

        $validator = $this->validator($request, $tugas);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tugas tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Upload file baru jika dikirim.
        if ($request->hasFile('file')) {
            // Hapus file lama.
            if ($tugas->file_path) {
                Storage::disk('public')->delete($tugas->file_path);
            }

            // Simpan file baru.
            $data['file_path'] = $request
                ->file('file')
                ->store('tugas', 'public');
        }

        $tugas->update($data);

        $tugas->load([
            'kelas',
            'mapel',
            'materi',
        ])->loadCount('pengumpulan');

        return response()->json([
            'message' => 'Tugas berhasil diperbarui.',
            'data' => $this->transform($tugas),
        ]);
    }

    /**
     * PATCH /api/guru/tugas/{tugas}/toggle-active
     */
    public function toggleActive(
        Request $request,
        Tugas $tugas
    ): JsonResponse {
        $this->authorizeOwnership($request, $tugas);

        $tugas->update([
            'is_active' => ! $tugas->is_active,
        ]);

        $tugas->load([
            'kelas',
            'mapel',
            'materi',
        ])->loadCount('pengumpulan');

        return response()->json([
            'message' => $tugas->is_active
                ? 'Tugas diaktifkan.'
                : 'Tugas dinonaktifkan.',
            'data' => $this->transform($tugas),
        ]);
    }

    /**
     * DELETE /api/guru/tugas/{tugas}
     */
    public function destroy(
        Request $request,
        Tugas $tugas
    ): JsonResponse {
        $this->authorizeOwnership($request, $tugas);

        // Hapus file tugas jika ada.
        if ($tugas->file_path) {
            Storage::disk('public')->delete($tugas->file_path);
        }

        $judul = $tugas->judul;

        $tugas->delete();

        return response()->json([
            'message' => "Tugas \"{$judul}\" berhasil dihapus.",
        ]);
    }

    /**
     * Pastikan guru yang login adalah pemilik tugas.
     */
    protected function authorizeOwnership(
        Request $request,
        Tugas $tugas
    ): void {
        abort_unless(
            $tugas->guru_id === $this->guruId($request),
            403,
            'Anda tidak memiliki akses ke tugas ini.'
        );
    }

    /**
     * Validasi data tugas.
     *
     * PENTING:
     * mapel_id menggunakan tabel `mapels`,
     * bukan `subjeks`.
     */
    protected function validator(
        Request $request,
        ?Tugas $tugas = null
    ): ValidatorContract {
        return Validator::make(
            $request->all(),
            [
                'kelas_id' => [
                    'required',
                    'integer',
                    Rule::exists('kelas', 'id'),
                ],

                'mapel_id' => [
                    'required',
                    'integer',
                    Rule::exists('mapels', 'id'),
                ],

                'materi_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('materis', 'id'),
                ],

                'judul' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'deskripsi' => [
                    'nullable',
                    'string',
                ],

                'instruksi' => [
                    'nullable',
                    'string',
                ],

                'deadline' => [
                    'required',
                    'date',
                ],

                'nilai_maksimal' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:1000',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],

                'file' => [
                    'nullable',
                    'file',
                    'max:10240',
                ],
            ],
            [
                'kelas_id.required' => 'Kelas wajib dipilih.',
                'kelas_id.exists' => 'Kelas yang dipilih tidak ditemukan.',

                'mapel_id.required' => 'Mata pelajaran wajib dipilih.',
                'mapel_id.exists' => 'Mata pelajaran yang dipilih tidak ditemukan.',

                'materi_id.exists' => 'Materi yang dipilih tidak ditemukan.',

                'judul.required' => 'Judul tugas wajib diisi.',
                'judul.max' => 'Judul tugas maksimal 255 karakter.',

                'deadline.required' => 'Deadline wajib diisi.',
                'deadline.date' => 'Format deadline tidak valid.',

                'nilai_maksimal.integer' => 'Nilai maksimal harus berupa angka.',
                'nilai_maksimal.min' => 'Nilai maksimal minimal 1.',
                'nilai_maksimal.max' => 'Nilai maksimal maksimal 1000.',

                'file.file' => 'File yang dikirim tidak valid.',
                'file.max' => 'Ukuran file maksimal 10 MB.',
            ]
        );
    }

    /**
     * Transform data tugas untuk response API.
     */
    protected function transform(Tugas $tugas): array
    {
        return [
            'id' => $tugas->id,

            'kelas_id' => $tugas->kelas_id,

            'guru_id' => $tugas->guru_id,

            'mapel_id' => $tugas->mapel_id,

            'materi_id' => $tugas->materi_id,

            'judul' => $tugas->judul,

            'deskripsi' => $tugas->deskripsi,

            'instruksi' => $tugas->instruksi,

            'file_path' => $tugas->file_path
                ? Storage::disk('public')->url($tugas->file_path)
                : null,

            'tanggal_dibuat' => $tugas->tanggal_dibuat
                ? $tugas->tanggal_dibuat->toIso8601String()
                : null,

            'deadline' => $tugas->deadline
                ? $tugas->deadline->toIso8601String()
                : null,

            'nilai_maksimal' => $tugas->nilai_maksimal,

            'is_active' => (bool) $tugas->is_active,

            'jumlah_pengumpulan' => $tugas->pengumpulan_count
                ?? $tugas->pengumpulan()->count(),

            'kelas' => $tugas->relationLoaded('kelas')
                ? $tugas->kelas
                : null,

            'mapel' => $tugas->relationLoaded('mapel')
                ? $tugas->mapel
                : null,

            'materi' => $tugas->relationLoaded('materi')
                ? $tugas->materi
                : null,

            'created_at' => $tugas->created_at
                ? $tugas->created_at->toIso8601String()
                : null,

            'updated_at' => $tugas->updated_at
                ? $tugas->updated_at->toIso8601String()
                : null,
        ];
    }
}
