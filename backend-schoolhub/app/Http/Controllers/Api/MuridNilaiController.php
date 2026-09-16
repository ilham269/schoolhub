<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Murid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MuridNilaiController extends Controller
{
    private const TASK_WEIGHT = 0.40;
    private const EXAM_WEIGHT = 0.60;
    private const DEFAULT_KKM = 75;

    public function index(Request $request): JsonResponse
    {
        $murid = Murid::where('user_id', $request->user()->id)->first();

        if (! $murid) {
            return response()->json(['success' => false, 'message' => 'Profil murid tidak ditemukan.'], 404);
        }

        $mapels = DB::table('class_subjects as cs')
            ->join('mapels as m', 'm.id', '=', 'cs.mapel_id')
            ->where('cs.kelas_id', $murid->kelas_id)
            ->select('m.id', 'm.kode_mapel', 'm.nama_mapel', 'm.kkm')
            ->get()
            ->keyBy('id')
            ->map(fn ($mapel) => [
                'mapel_id' => $mapel->id,
                'kode_mapel' => $mapel->kode_mapel,
                'nama_mapel' => $mapel->nama_mapel,
                'kkm' => (int) ($mapel->kkm ?? self::DEFAULT_KKM),
                'task_scores' => [],
                'exam_scores' => [],
            ])->all();

        $tugas = DB::table('tugas as t')
            ->join('mapels as m', 'm.id', '=', 't.mapel_id')
            ->leftJoin('pengumpulan_tugas as p', function ($join) use ($murid) {
                $join->on('p.tugas_id', '=', 't.id')->where('p.murid_id', '=', $murid->id);
            })
            ->where('t.kelas_id', $murid->kelas_id)
            ->where('t.is_active', true)
            ->select('t.id', 't.judul', 't.deadline', 't.mapel_id', 'm.nama_mapel', 'p.tanggal_pengumpulan', 'p.nilai', 'p.feedback', 'p.status')
            ->orderByDesc('t.deadline')
            ->get();

        $completedTasks = [];
        $pendingTasks = [];
        $taskScores = [];
        foreach ($tugas as $tugasItem) {
            $hasSubmission = $tugasItem->tanggal_pengumpulan !== null;
            $isLate = $hasSubmission && $tugasItem->tanggal_pengumpulan > $tugasItem->deadline;
            $item = [
                'id' => $tugasItem->id,
                'judul' => $tugasItem->judul,
                'mapel' => $tugasItem->nama_mapel,
                'deadline' => $tugasItem->deadline,
                'tanggal_pengumpulan' => $tugasItem->tanggal_pengumpulan,
                'nilai' => $tugasItem->nilai === null ? null : (float) $tugasItem->nilai,
                'feedback' => $tugasItem->feedback,
                'status' => $tugasItem->status,
                'terlambat' => $isLate,
            ];

            if ($hasSubmission) {
                $completedTasks[] = $item;
                if ($tugasItem->nilai !== null) {
                    $mapels[$tugasItem->mapel_id]['task_scores'][] = (float) $tugasItem->nilai;
                    $taskScores[] = (float) $tugasItem->nilai;
                }
            } else {
                $item['lewat_deadline'] = now()->greaterThan($tugasItem->deadline);
                $pendingTasks[] = $item;
            }
        }

        $attempts = DB::table('exam_attempts as ea')
            ->join('exams as e', 'e.id', '=', 'ea.exam_id')
            ->join('mapels as m', 'm.id', '=', 'e.mapel_id')
            ->where('ea.murid_id', $murid->id)
            ->select('e.mapel_id', 'm.kode_mapel', 'm.nama_mapel', 'm.kkm', 'e.title', 'e.type', 'e.start_at', 'ea.submitted_at', 'ea.score', 'ea.grade', 'ea.status')
            ->orderByDesc('e.start_at')
            ->get();

        $examHistory = [];
        $examScores = [];
        foreach ($attempts as $attempt) {
            $isScored = $attempt->score !== null && in_array($attempt->status, ['Submitted', 'Graded'], true);
            $examHistory[] = [
                'title' => $attempt->title,
                'type' => $attempt->type,
                'mapel' => $attempt->nama_mapel,
                'tanggal' => $attempt->submitted_at ?? $attempt->start_at,
                'score' => $attempt->score === null ? null : (float) $attempt->score,
                'grade' => $attempt->grade === null ? null : (float) $attempt->grade,
                'status' => $attempt->status,
            ];
            if ($isScored) {
                if (! isset($mapels[$attempt->mapel_id])) {
                    $mapels[$attempt->mapel_id] = ['mapel_id' => $attempt->mapel_id, 'kode_mapel' => $attempt->kode_mapel, 'nama_mapel' => $attempt->nama_mapel, 'kkm' => (int) ($attempt->kkm ?? self::DEFAULT_KKM), 'task_scores' => [], 'exam_scores' => []];
                }
                if (isset($mapels[$attempt->mapel_id])) $mapels[$attempt->mapel_id]['exam_scores'][] = (float) $attempt->score;
                $examScores[] = (float) $attempt->score;
            }
        }

        $breakdown = collect($mapels)->map(function (array $mapel) {
            $taskAverage = count($mapel['task_scores']) ? round(array_sum($mapel['task_scores']) / count($mapel['task_scores']), 2) : null;
            $examAverage = count($mapel['exam_scores']) ? round(array_sum($mapel['exam_scores']) / count($mapel['exam_scores']), 2) : null;
            $final = $taskAverage !== null && $examAverage !== null
                ? round(($taskAverage * self::TASK_WEIGHT) + ($examAverage * self::EXAM_WEIGHT), 2)
                : ($taskAverage ?? $examAverage);

            return [...collect($mapel)->except(['task_scores', 'exam_scores'])->all(), 'rata_tugas' => $taskAverage, 'rata_ujian' => $examAverage, 'nilai_akhir' => $final, 'tuntas' => $final !== null && $final >= $mapel['kkm']];
        })->values();

        $scoredMapels = $breakdown->filter(fn ($mapel) => $mapel['nilai_akhir'] !== null);
        $allScores = [...$taskScores, ...$examScores];
        $trend = collect([...$completedTasks, ...$examHistory])
            ->filter(fn ($item) => ($item['nilai'] ?? $item['score'] ?? null) !== null)
            ->map(fn ($item) => ['label' => $item['tanggal_pengumpulan'] ?? $item['tanggal'], 'nilai' => $item['nilai'] ?? $item['score']])
            ->sortBy('label')->values();

        return response()->json(['success' => true, 'data' => [
            'weights' => ['tugas' => self::TASK_WEIGHT, 'ujian' => self::EXAM_WEIGHT],
            'summary' => ['rata_rata' => count($allScores) ? round(array_sum($allScores) / count($allScores), 2) : null, 'tugas_selesai' => count($completedTasks), 'tugas_total' => $tugas->count(), 'ujian_diambil' => $attempts->count(), 'mapel_tuntas' => $scoredMapels->where('tuntas', true)->count(), 'mapel_perlu_perhatian' => $scoredMapels->where('tuntas', false)->count()],
            'breakdown' => $breakdown,
            'tugas_selesai' => $completedTasks,
            'tugas_belum_dikumpulkan' => $pendingTasks,
            'ujian' => $examHistory,
            'trend' => $trend,
        ]]);
    }
}
