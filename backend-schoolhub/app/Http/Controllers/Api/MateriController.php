<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class MateriController extends Controller
{
    public function index(Request $request)
    {
        $query = Materi::query()->with(['kelas', 'guru.user', 'mapel']);

        if ($request->user()->role === 'guru') {
            $guru = $request->user()->guru;
            abort_unless($guru, 403, 'Profil guru tidak ditemukan.');
            $query->where('guru_id', $guru->id);
        } else {
            $murid = $request->user()->murid;
            abort_unless($murid, 403, 'Profil murid tidak ditemukan.');
            $query->where('kelas_id', $murid->kelas_id)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        return response()->json(['data' => $query->latest('published_at')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $guru = $request->user()->guru;
        abort_unless($guru, 403, 'Profil guru tidak ditemukan.');

        $data = $this->validated($request);
        $data['guru_id'] = $guru->id;
        $data['file'] = $request->hasFile('file')
            ? $request->file('file')->store('materi', 'local')
            : null;

        $materi = Materi::create($data);

        return response()->json(['message' => 'Materi berhasil dibuat.', 'data' => $materi->load(['kelas', 'guru.user', 'mapel'])], 201);
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
            Storage::disk('local')->delete($materi->file);
            $data['file'] = $request->file('file')->store('materi', 'local');
        }

        $materi->update($data);
        return response()->json(['message' => 'Materi berhasil diperbarui.', 'data' => $materi->fresh()->load(['kelas', 'guru.user', 'mapel'])]);
    }

    public function destroy(Request $request, Materi $materi)
    {
        $this->authorizeGuru($request, $materi);
        Storage::disk('local')->delete($materi->file);
        $materi->delete();

        return response()->json(['message' => 'Materi berhasil dihapus.']);
    }

    public function download(Request $request, Materi $materi)
    {
        $this->authorizeAccess($request, $materi);
        abort_unless($materi->file && Storage::disk('local')->exists($materi->file), 404, 'File materi tidak ditemukan.');

        return Storage::disk('local')->download($materi->file);
    }

    private function validated(Request $request, ?Materi $materi = null): array
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => [$materi ? 'sometimes' : 'required', 'integer', 'exists:kelas,id'],
            'mapel_id' => [$materi ? 'sometimes' : 'required', 'integer', 'exists:mapels,id'],
            'judul' => [$materi ? 'sometimes' : 'required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip', 'max:10240'],
            'link' => ['nullable', 'url', 'max:2048'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validator->after(function ($validator) use ($request, $materi) {
            $hasFile = $request->hasFile('file') || $materi?->file;
            $hasLink = $request->has('link') ? filled($request->input('link')) : $materi?->link;
            $hasAttachment = $hasFile || $hasLink;
            if (! $hasAttachment) {
                $validator->errors()->add('file', 'Materi harus memiliki file atau link.');
            }
        });

        return $validator->validate();
    }

    private function authorizeAccess(Request $request, Materi $materi): void
    {
        if (strtolower($request->user()->role) === 'guru') {
            $this->authorizeGuru($request, $materi);
            return;
        }

        $murid = $request->user()->murid;
        abort_unless($murid && $materi->kelas_id === $murid->kelas_id && $materi->published_at && $materi->published_at->lte(now()), 403, 'Anda tidak memiliki akses ke materi ini.');
    }

    private function authorizeGuru(Request $request, Materi $materi): void
    {
        $guru = $request->user()->guru;
        abort_unless($guru && $materi->guru_id === $guru->id, 403, 'Anda tidak memiliki akses ke materi ini.');
    }
}
