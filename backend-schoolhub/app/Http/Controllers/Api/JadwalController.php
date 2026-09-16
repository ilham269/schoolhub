<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\Jadwal;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Jadwal::query()
            ->with(['kelas', 'guru', 'subjek'])
            ->orderBy('hari')
            ->orderBy('jam_mulai');

        $user = $request->user();

        if (AcademicAccess::isGuru($user)) {
            $query->where('guru_id', AcademicAccess::guruId($user) ?: 0);
        } elseif (AcademicAccess::isMurid($user)) {
            $query->where('kelas_id', AcademicAccess::kelasId($user) ?: 0);
        } elseif (AcademicAccess::isAdmin($user)) {
            foreach (['kelas_id', 'guru_id', 'mapel_id'] as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->integer($filter));
                }
            }
        }

        if ($request->filled('hari')) {
            $query->where('hari', $request->query('hari'));
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $page = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal berhasil diambil',
            'data' => collect($page->items())->map(fn (Jadwal $jadwal) => $this->transform($jadwal))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(StoreJadwalRequest $request): JsonResponse
    {
        $jadwal = Jadwal::create([
            ...$request->only(['kelas_id', 'guru_id', 'mapel_id', 'hari', 'jam_mulai', 'jam_selesai', 'ruang']),
            'jam_mulai' => $request->normalizedTime('jam_mulai'),
            'jam_selesai' => $request->normalizedTime('jam_selesai'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dibuat',
            'data' => $this->transform($jadwal->load(['kelas', 'guru', 'subjek'])),
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $jadwal = Jadwal::with(['kelas', 'guru', 'subjek'])->find($id);

        if (! $jadwal || ! $this->canView($request, $jadwal)) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail jadwal berhasil diambil',
            'data' => $this->transform($jadwal),
        ]);
    }

    public function update(UpdateJadwalRequest $request, $id): JsonResponse
    {
        $jadwal = Jadwal::find($id);

        if (! $jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        $jadwal->update([
            'kelas_id' => $request->integer('kelas_id'),
            'guru_id' => $request->integer('guru_id'),
            'mapel_id' => $request->integer('mapel_id'),
            'hari' => $request->input('hari'),
            'jam_mulai' => $request->normalizedTime('jam_mulai'),
            'jam_selesai' => $request->normalizedTime('jam_selesai'),
            'ruang' => $request->input('ruang'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $jadwal->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diupdate',
            'data' => $this->transform($jadwal->fresh(['kelas', 'guru', 'subjek'])),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $jadwal = Jadwal::find($id);

        if (! $jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus',
        ]);
    }

    public function byKelas(Request $request, $kelasId): JsonResponse
    {
        if (AcademicAccess::isMurid($request->user()) && AcademicAccess::kelasId($request->user()) != $kelasId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke resource ini.',
            ], 403);
        }

        $jadwals = Jadwal::with(['guru', 'subjek'])
            ->where('kelas_id', $kelasId)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (Jadwal $jadwal) => $this->transform($jadwal));

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berdasarkan kelas berhasil diambil',
            'data' => $jadwals,
        ]);
    }

    public function byGuru(Request $request, $guruId): JsonResponse
    {
        if (AcademicAccess::isGuru($request->user()) && AcademicAccess::guruId($request->user()) != $guruId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke resource ini.',
            ], 403);
        }

        $jadwals = Jadwal::with(['kelas', 'subjek'])
            ->where('guru_id', $guruId)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (Jadwal $jadwal) => $this->transform($jadwal));

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berdasarkan guru berhasil diambil',
            'data' => $jadwals,
        ]);
    }

    public function byHari(Request $request, $hari): JsonResponse
    {
        $query = Jadwal::with(['kelas', 'guru', 'subjek'])
            ->where('hari', $hari)
            ->orderBy('jam_mulai');

        $user = $request->user();
        if (AcademicAccess::isGuru($user)) {
            $query->where('guru_id', AcademicAccess::guruId($user) ?: 0);
        } elseif (AcademicAccess::isMurid($user)) {
            $query->where('kelas_id', AcademicAccess::kelasId($user) ?: 0);
        }

        return response()->json([
            'success' => true,
            'message' => "Jadwal hari {$hari} berhasil diambil",
            'data' => $query->get()->map(fn (Jadwal $jadwal) => $this->transform($jadwal)),
        ]);
    }

    private function canView(Request $request, Jadwal $jadwal): bool
    {
        $user = $request->user();

        if (AcademicAccess::isAdmin($user)) {
            return true;
        }

        if (AcademicAccess::isGuru($user)) {
            return $jadwal->guru_id === AcademicAccess::guruId($user);
        }

        if (AcademicAccess::isMurid($user)) {
            return $jadwal->kelas_id === AcademicAccess::kelasId($user);
        }

        return false;
    }

    private function transform(Jadwal $jadwal): array
    {
        return [
            'id' => $jadwal->id,
            'kelas_id' => $jadwal->kelas_id,
            'guru_id' => $jadwal->guru_id,
            'mapel_id' => $jadwal->mapel_id,
            'hari' => $jadwal->hari,
            'jam_mulai' => substr((string) $jadwal->jam_mulai, 0, 5),
            'jam_selesai' => substr((string) $jadwal->jam_selesai, 0, 5),
            'ruang' => $jadwal->ruang,
            'is_active' => (bool) $jadwal->is_active,
            'kelas' => $jadwal->kelas,
            'guru' => $jadwal->guru,
            'subjek' => $jadwal->subjek,
            'kelas_name' => $jadwal->kelas?->name,
            'guru_name' => $jadwal->guru?->nama_lengkap_guru ?? $jadwal->guru?->user?->name,
            'mapel_name' => $jadwal->subjek?->nama_mapel,
            'created_at' => $jadwal->created_at,
            'updated_at' => $jadwal->updated_at,
        ];
    }
}
