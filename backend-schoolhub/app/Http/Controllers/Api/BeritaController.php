<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BeritaController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success(News::latest('published_at')->latest()->get());
    }

    public function published(): JsonResponse
    {
        return $this->success($this->publishedQuery()->get());
    }

    public function latest(int $limit = 5): JsonResponse
    {
        $limit = max(1, min($limit, 20));

        return $this->success($this->publishedQuery()->limit($limit)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);
        $data['excerpt'] = $data['excerpt'] ?? Str::limit($data['content'], 180);
        $data['author'] = $request->user()?->name ?? 'Admin Sekolah';
        $data['is_published'] = $data['is_published'] ?? true;
        $data['published_at'] = $data['published_at'] ?? ($data['is_published'] ? now() : null);

        $news = News::create($data);

        return response()->json(['success' => true, 'message' => 'Berita berhasil dibuat', 'data' => $news], 201);
    }

    public function show(int $id): JsonResponse
    {
        $news = News::findOrFail($id);
        $news->increment('views');

        return $this->success($news->fresh());
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $news = News::where('slug', $slug)->firstOrFail();
        $news->increment('views');

        return $this->success($news->fresh());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $news = News::findOrFail($id);
        $data = $this->validated($request, true, $news->id);

        if (array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $news->id);
        }
        if (isset($data['title']) && !isset($data['excerpt'])) {
            $data['excerpt'] = $news->excerpt ?? Str::limit($data['content'] ?? $news->content, 180);
        }
        if (($data['is_published'] ?? $news->is_published) && !$news->published_at && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        $news->update($data);

        return $this->success($news->fresh(), 'Berita berhasil diperbarui');
    }

    public function destroy(int $id): JsonResponse
    {
        News::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Berita berhasil dihapus']);
    }

    public function byKategori(string $kategori): JsonResponse
    {
        return $this->success($this->publishedQuery()->where('category', $kategori)->get());
    }

    private function publishedQuery()
    {
        return News::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');
    }

    private function validated(Request $request, bool $partial = false, ?int $ignoreId = null): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('news', 'slug')->ignore($ignoreId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => [$required, 'string'],
            'image' => ['nullable', 'string', 'max:2048'],
            'category' => ['nullable', Rule::in(['Akademik', 'Kegiatan', 'Prestasi', 'Umum'])],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'berita';
        $slug = $base;
        $suffix = 2;

        while (News::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function success(mixed $data, string $message = 'Data berita berhasil diambil'): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }
}
