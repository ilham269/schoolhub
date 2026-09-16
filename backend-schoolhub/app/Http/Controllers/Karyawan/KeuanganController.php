<?php

declare(strict_types=1);

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateKuitansiPdf;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\SlipGaji;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    public function dashboard(): \Illuminate\Http\JsonResponse
    {
        $start = now()->startOfMonth(); $end = now()->endOfMonth();
        $base = TagihanSpp::whereBetween('periode', [$start->toDateString(), $end->toDateString()]);
        $summary = [
            'total_tagihan' => (clone $base)->count(),
            'lunas' => (clone $base)->where('status', 'LUNAS')->count(),
            'belum_bayar' => (clone $base)->whereIn('status', ['UNPAID', 'PENDING'])->count(),
            'terkumpul' => Pembayaran::where('status', 'SUCCESS')->whereBetween('paid_at', [$start, $end])->sum('gross_amount'),
        ];
        $perKelas = Kelas::withCount(['murids as tagihan_lunas_count' => fn ($q) => $q->whereHas('tagihanSpps', fn ($t) => $t->whereBetween('periode', [$start->toDateString(), $end->toDateString()])->where('status', 'LUNAS'))])->get();
        return response()->json(['summary' => $summary, 'per_kelas' => $perKelas]);
    }

    public function daftarTagihan(Request $request): \Illuminate\Http\JsonResponse
    {
        $tagihans = TagihanSpp::with(['murid.user', 'murid.kelas'])
            ->when($request->filled('kelas'), fn ($q) => $q->whereHas('murid', fn ($m) => $m->where('kelas_id', $request->kelas)))
            ->when($request->filled('periode'), fn ($q) => $q->whereDate('periode', $request->periode))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('murid', fn ($m) => $m->where('nis', 'like', "%{$request->q}%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->q}%"))))
            ->latest('periode')->paginate(20)->withQueryString();
        return response()->json(['data' => $tagihans, 'kelas' => Kelas::orderBy('name')->get()]);
    }

    public function detailTagihan(TagihanSpp $tagihan): \Illuminate\Http\JsonResponse
    {
        $tagihan->load(['murid.user', 'murid.kelas', 'pembayarans' => fn ($q) => $q->latest()]);
        return response()->json(['data' => $tagihan]);
    }

    public function updateManual(Request $request, TagihanSpp $tagihan): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);
        abort_if($tagihan->status === 'LUNAS', 422, 'Tagihan sudah lunas.');
        DB::transaction(function () use ($request, $tagihan, $data): void {
            $note = trim(($tagihan->notes ? $tagihan->notes."\n" : '').'Override tunai LUNAS oleh user #'.$request->user()->id.' pada '.now()->toDateTimeString().($data['notes'] ? ': '.$data['notes'] : ''));
            $tagihan->update(['status' => 'LUNAS', 'paid_at' => now(), 'notes' => $note]);
            Pembayaran::create(['tagihan_id' => $tagihan->id, 'gateway' => 'manual', 'transaction_id' => 'MANUAL-'.$tagihan->id.'-'.now()->format('YmdHis'), 'payment_type' => 'cash', 'gross_amount' => $tagihan->total, 'status' => 'SUCCESS', 'paid_at' => now(), 'signature_verified' => true]);
            GenerateKuitansiPdf::dispatch($tagihan->id)->afterCommit();
        });
        return response()->json(['message' => 'Pembayaran manual dicatat dan kuitansi sedang dibuat.', 'data' => $tagihan->fresh()]);
    }

    public function kelolaSlipGaji(): \Illuminate\Http\JsonResponse
    {
        return response()->json(SlipGaji::with('karyawan.user')->latest('periode')->paginate(20));
    }
}
