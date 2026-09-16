<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMapelRequest;
use App\Http\Requests\UpdateMapelRequest;
use App\Models\Subjek;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subjek::query()->orderBy('nama_mapel');
        $user = $request->user();

        if ($search = $request->query('search', $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_mapel', 'like', "%{$search}%")
                    ->orWhere('nama_mapel', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if (AcademicAccess::isGuru($user)) {
            $guruId = AcademicAccess::guruId($user);
            $query->whereHas('subjekgurus', fn ($q) => $q->where('guru_id', $guruId ?: 0));
        } elseif (AcademicAccess::isMurid($user)) {
            $kelasId = AcademicAccess::kelasId($user);
            $query->whereHas('subjekkelas', fn ($q) => $q->where('kelas_id', $kelasId ?: 0));
        }

        return $this->paginated($query, $request, 'Data mapel berhasil diambil');
    }

    public function store(StoreMapelRequest $request): JsonResponse
    {
        $data = $request->validated();
        $mapel = Subjek::create([
            ...$data,
            'jumlah_jam' => $data['jumlah_jam'] ?? 2,
            'kkm' => $data['kkm'] ?? 75,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil dibuat',
            'data' => $mapel,
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $mapel = Subjek::with(['gurus', 'kelas'])->find($id);

        if (! $mapel || ! $this->canViewMapel($request, $mapel)) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail mapel berhasil diambil',
            'data' => $mapel,
        ]);
    }

    public function update(UpdateMapelRequest $request, $id): JsonResponse
    {
        $mapel = Subjek::find($id);

        if (! $mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak ditemukan',
            ], 404);
        }

        $mapel->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil diupdate',
            'data' => $mapel->fresh(),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $mapel = Subjek::find($id);

        if (! $mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak ditemukan',
            ], 404);
        }

        if ($mapel->jadwals()->exists() || $mapel->materis()->exists() || $mapel->tugas()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak dapat dihapus karena masih digunakan. Nonaktifkan mapel sebagai gantinya.',
            ], 422);
        }

        if ($mapel->subjekgurus()->exists() || $mapel->subjekkelas()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Mapel tidak dapat dihapus karena masih memiliki mapping guru atau kelas.',
            ], 422);
        }

        $mapel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mapel berhasil dihapus',
        ]);
    }

    public function active(): JsonResponse
    {
        $mapels = Subjek::where('is_active', true)->orderBy('nama_mapel')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel aktif berhasil diambil',
            'data' => $mapels,
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

        $mapels = Subjek::whereHas('subjekkelas', fn ($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('nama_mapel')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel berdasarkan kelas berhasil diambil',
            'data' => $mapels,
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

        $mapels = Subjek::whereHas('subjekgurus', fn ($q) => $q->where('guru_id', $guruId))
            ->orderBy('nama_mapel')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data mapel berdasarkan guru berhasil diambil',
            'data' => $mapels,
        ]);
    }

    private function canViewMapel(Request $request, Subjek $mapel): bool
    {
        $user = $request->user();

        if (AcademicAccess::isAdmin($user)) {
            return true;
        }

        if (AcademicAccess::isGuru($user)) {
            return $mapel->subjekgurus()->where('guru_id', AcademicAccess::guruId($user))->exists();
        }

        if (AcademicAccess::isMurid($user)) {
            return $mapel->subjekkelas()->where('kelas_id', AcademicAccess::kelasId($user))->exists();
        }

        return false;
    }

    private function paginated($query, Request $request, string $message): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $page = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
