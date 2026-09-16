<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjekGuruRequest;
use App\Models\Jadwal;
use App\Models\Subjekguru;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjekGuruController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subjekguru::query()
            ->with(['guru', 'subjek'])
            ->orderByDesc('id');

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->integer('guru_id'));
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->integer('mapel_id'));
        }

        $user = $request->user();
        if (AcademicAccess::isGuru($user)) {
            $query->where('guru_id', AcademicAccess::guruId($user) ?: 0);
        } elseif (AcademicAccess::isMurid($user)) {
            $kelasId = AcademicAccess::kelasId($user);
            $query->whereHas('subjek.subjekkelas', fn ($q) => $q->where('kelas_id', $kelasId ?: 0));
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $page = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data mapping guru-mapel berhasil diambil',
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(StoreSubjekGuruRequest $request): JsonResponse
    {
        $mapping = Subjekguru::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mapping guru-mapel berhasil dibuat',
            'data' => $mapping->load(['guru', 'subjek']),
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        $mapping = Subjekguru::find($id);

        if (! $mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping tidak ditemukan',
            ], 404);
        }

        $inUse = Jadwal::where('guru_id', $mapping->guru_id)
            ->where('mapel_id', $mapping->mapel_id)
            ->exists();

        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping tidak dapat dihapus karena masih digunakan pada jadwal.',
            ], 422);
        }

        $mapping->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mapping guru-mapel berhasil dihapus',
        ]);
    }
}
