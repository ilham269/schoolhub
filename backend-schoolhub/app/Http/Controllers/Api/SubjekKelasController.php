<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjekKelasRequest;
use App\Models\Jadwal;
use App\Models\Subjekkelas;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjekKelasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subjekkelas::query()
            ->with(['kelas', 'subjek'])
            ->orderByDesc('id');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->integer('mapel_id'));
        }

        $user = $request->user();
        if (AcademicAccess::isGuru($user)) {
            $guruId = AcademicAccess::guruId($user);
            $query->whereHas('subjek.subjekgurus', fn ($q) => $q->where('guru_id', $guruId ?: 0));
        } elseif (AcademicAccess::isMurid($user)) {
            $query->where('kelas_id', AcademicAccess::kelasId($user) ?: 0);
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $page = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data mapping kelas-mapel berhasil diambil',
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(StoreSubjekKelasRequest $request): JsonResponse
    {
        $mapping = Subjekkelas::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mapping kelas-mapel berhasil dibuat',
            'data' => $mapping->load(['kelas', 'subjek']),
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        $mapping = Subjekkelas::find($id);

        if (! $mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping tidak ditemukan',
            ], 404);
        }

        $inUse = Jadwal::where('kelas_id', $mapping->kelas_id)
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
            'message' => 'Mapping kelas-mapel berhasil dihapus',
        ]);
    }
}
