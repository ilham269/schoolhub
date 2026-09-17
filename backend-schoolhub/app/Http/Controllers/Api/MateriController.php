<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MateriController extends Controller
{
    public function index(Request $request)
    {
        $role = strtolower($request->user()->role ?? '');
        $query = Materi::query()->with(['kelas', 'guru.user', 'mapel']);

        if ($role === 'guru') {
            $guru = $request->user()->guru;
            abort_unless($guru, 403, 'Profil guru tidak ditemukan.');
            $query->where('guru_id', $guru->id);
        } elseif ($role === 'murid') {
            $murid = $request->user()->murid;
            abort_unless($murid, 403, 'Profil murid tidak ditemukan.');

            if (! $murid->kelas_id) {
                return response()->json([
                    'message' => 'Akun murid belum terhubung ke kelas, hubungi admin.',
                    'data' => [],
                ], 422);
            }

            $query->where('kelas_id', $murid->kelas_id)
                ->where('is_published', true);
        } else {
            abort(403, 'Anda tidak memiliki akses ke resource ini.');
        }

        return response()->json(['data' => $query->latest()->get()]);
    }

    public function store(Request $request)
    {
        $guru = $request->user()->guru;
        abort_unless($guru, 403, 'Profil guru tidak ditemukan.');

        $data = $this->validated($request);
        $data['guru_id'] = $guru->id;
        $data['is_published'] = $data['is_published'] ?? true;
        $data['tanggal_upload'] = now()->toDateString();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('materi', 'local');
        }
        unset($data['file']);

        $materi = Materi::create($data);

        return response()->json([
            'message' => 'Materi berhasil dibuat.',
            'data' => $materi->load(['kelas', 'guru.user', 'mapel']),
        ], 201);
    }

    public function show(Request $request, Materi $materi)
    {
        $this->authorizeAccess($request, $materi);
        return response()->json(['data' => $materi->load(['kelas', 'guru.user', 'mapel'])]);
    }

    public function update(Request $request, Materi $materi)
    {
        $this->authorizeGuru($request, $materi);
        $data = $this->validated($request, $materi);

        if ($request->hasFile('file')) {
            if ($materi->file_path) {
                Storage::disk('local')->delete($materi->file_path);
            }
            $data['file_path'] = $request->file('file')->store('materi', 'local');
        }
        unset($data['file']);

        $materi->update($data);

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data' => $materi->fresh()->load(['kelas', 'guru.user', 'mapel']),
        ]);
    }

    public function destroy(Request $request, Materi $materi)
    {
        $this->authorizeGuru($request, $materi);

        if ($materi->file_path) {
            Storage::disk('local')->delete($materi->file_path);
        }

        $materi->delete();

        return response()->json(['message' => 'Materi berhasil dihapus.']);
    }

    public function download(Request $request, Materi $materi)
    {
        $this->authorizeAccess($request, $materi);
        abort_unless(
            $materi->file_path && Storage::disk('local')->exists($materi->file_path),
            404,
            'File materi tidak ditemukan.'
        );

        // Kirim dengan nama file asli (ambil dari path, fallback ke judul)
        $originalName = $materi->judul . '.' . pathinfo($materi->file_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($materi->file_path, $originalName);
    }

    private function validated(Request $request, ?Materi $materi = null): array
    {
        $isCreate = $materi === null;

        $validator = Validator::make($request->all(), [
            'kelas_id'    => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:kelas,id'],
            'mapel_id'    => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:mapels,id'],
            'judul'       => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'deskripsi'   => ['nullable', 'string'],
            'konten'      => ['nullable', 'string'],
            'file'        => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip', 'max:10240'],
            'link'        => ['nullable', 'url', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($v) use ($request, $materi) {
            $hasFile = $request->hasFile('file') || $materi?->file_path;
            $hasLink = filled($request->input('link')) || $materi?->link;
            if (! $hasFile && ! $hasLink) {
                $v->errors()->add('file', 'Materi harus memiliki file atau link.');
            }
        });

        return $validator->validate();
    }

    private function authorizeAccess(Request $request, Materi $materi): void
    {
        $role = strtolower($request->user()->role ?? '');

        if ($role === 'guru') {
            $this->authorizeGuru($request, $materi);
            return;
        }

        if ($role === 'murid') {
            $murid = $request->user()->murid;
            abort_unless(
                $murid && $materi->kelas_id === $murid->kelas_id && $materi->is_published,
                403,
                'Anda tidak memiliki akses ke materi ini.'
            );
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke resource ini.');
    }

    private function authorizeGuru(Request $request, Materi $materi): void
    {
        $guru = $request->user()->guru;
        abort_unless($guru && $materi->guru_id === $guru->id, 403, 'Anda tidak memiliki akses ke materi ini.');
    }
}
