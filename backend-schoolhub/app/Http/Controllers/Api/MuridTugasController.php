<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengumpulantugas;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MuridTugasController extends Controller
{
    public function index(Request $request)
    {
        $murid = $request->user()->murid;
        abort_unless($murid, 404, 'Profil murid tidak ditemukan.');
        abort_if($murid->kelas_id === null, 422, 'Akun murid belum terhubung ke kelas.');

        $tugas = Tugas::query()
            ->with(['mapel', 'guru.user'])
            ->where('kelas_id', $murid->kelas_id)
            ->where('is_active', true)
            ->latest('deadline')
            ->get()
            ->map(function (Tugas $tugas) use ($murid) {
                $pengumpulan = Pengumpulantugas::query()
                    ->where('tugas_id', $tugas->id)
                    ->where('murid_id', $murid->id)
                    ->first();

                return [
                    ...$tugas->toArray(),
                    'mapel_nama' => $tugas->mapel?->nama_mapel,
                    'pengumpulan' => $pengumpulan ? [
                        'id' => $pengumpulan->id,
                        'file_path' => $pengumpulan->file_path,
                        'link' => $pengumpulan->link,
                        'catatan' => $pengumpulan->catatan,
                        'dikumpulkan_at' => $pengumpulan->tanggal_pengumpulan,
                        'nilai' => $pengumpulan->nilai,
                        'feedback' => $pengumpulan->feedback,
                        'status' => $pengumpulan->status,
                    ] : null,
                ];
            });

        return response()->json(['data' => $tugas]);
    }

    public function submit(Request $request, Tugas $tugas)
    {
        $murid = $request->user()->murid;
        abort_unless($murid, 404, 'Profil murid tidak ditemukan.');
        abort_if($murid->kelas_id === null, 422, 'Akun murid belum terhubung ke kelas.');
        abort_unless($tugas->kelas_id === $murid->kelas_id && $tugas->is_active, 403, 'Anda tidak dapat mengumpulkan tugas ini.');

        $data = Validator::make($request->all(), [
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png', 'max:10240'],
            'link' => ['nullable', 'url', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ])->after(function ($validator) use ($request) {
            if (! $request->hasFile('file') && ! filled($request->input('link'))) {
                $validator->errors()->add('file', 'Unggah file jawaban atau masukkan link jawaban.');
            }
        })->validate();

        $pengumpulan = Pengumpulantugas::firstOrNew(['tugas_id' => $tugas->id, 'murid_id' => $murid->id]);
        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($pengumpulan->file_path);
            $pengumpulan->file_path = $request->file('file')->store('pengumpulan-tugas', 'local');
        }
        $pengumpulan->link = $data['link'] ?? null;
        $pengumpulan->catatan = $data['catatan'] ?? null;
        $pengumpulan->tanggal_pengumpulan = now();
        $pengumpulan->status = now()->greaterThan($tugas->deadline) ? 'Terlambat' : 'Belum Dinilai';
        $pengumpulan->save();

        return response()->json(['message' => 'Jawaban berhasil dikumpulkan.', 'data' => [
            'id' => $pengumpulan->id,
            'file_path' => $pengumpulan->file_path,
            'link' => $pengumpulan->link,
            'catatan' => $pengumpulan->catatan,
            'dikumpulkan_at' => $pengumpulan->tanggal_pengumpulan,
            'nilai' => $pengumpulan->nilai,
            'feedback' => $pengumpulan->feedback,
            'status' => $pengumpulan->status,
        ]]);
    }
}
