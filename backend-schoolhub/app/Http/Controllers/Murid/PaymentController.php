<?php

declare(strict_types=1);

namespace App\Http\Controllers\Murid;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\TagihanSpp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->validateStatus($request->query('status'));
        $murid = $request->user()->murid;

        $tagihans = TagihanSpp::query()->where('murid_id', $murid->id)
            ->with(['pembayarans' => fn ($query) => $query->latest()->limit(1)])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest('periode')->paginate(12)->withQueryString();

        return response()->json($tagihans);
    }

    public function bayar(Request $request, TagihanSpp $tagihan, PaymentGatewayInterface $gateway): JsonResponse
    {
        $this->authorize('bayar', $tagihan);
        abort_unless(in_array($tagihan->status, ['UNPAID', 'PENDING'], true), 422, 'Tagihan ini tidak dapat dibayar.');

        try {
            $tagihan->loadMissing('murid.user');
            $transaction = $gateway->createTransaction($tagihan);
            if (!($transaction['success'] ?? false)) {
                return response()->json(['message' => $transaction['message'] ?? 'Gagal membuat transaksi pembayaran.'], 502);
            }

            DB::transaction(function () use ($tagihan, $transaction): void {
                Pembayaran::create([
                    'tagihan_id' => $tagihan->id, 'gateway' => 'midtrans',
                    'transaction_id' => $transaction['order_id'], 'snap_token' => $transaction['snap_token'],
                    'gross_amount' => $tagihan->total, 'status' => 'PENDING',
                ]);
                $tagihan->update(['status' => 'PENDING']);
            });

            return response()->json(['snap_token' => $transaction['snap_token'], 'order_id' => $transaction['order_id']]);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat pembayaran SPP', ['tagihan_id' => $tagihan->id, 'error' => $exception->getMessage()]);
            return response()->json(['message' => 'Transaksi tidak dapat dibuat. Silakan coba lagi.'], 500);
        }
    }

    public function riwayat(Request $request): JsonResponse
    {
        $payments = Pembayaran::with('tagihan')->whereHas('tagihan', fn ($query) => $query->where('murid_id', $request->user()->murid->id))
            ->latest()->paginate(12);
        return response()->json($payments);
    }

    public function downloadKuitansi(Request $request, TagihanSpp $tagihan)
    {
        $this->authorize('download', $tagihan);
        abort_unless($tagihan->status === 'LUNAS' && $tagihan->kuitansi_path, 404, 'Kuitansi belum tersedia.');
        abort_unless(Storage::disk('public')->exists($tagihan->kuitansi_path), 404, 'File kuitansi tidak ditemukan.');
        return Storage::disk('public')->download($tagihan->kuitansi_path, $tagihan->invoice_number . '.pdf');
    }

    private function validateStatus(?string $status): void
    {
        abort_if($status !== null && !in_array($status, ['UNPAID', 'PENDING', 'LUNAS', 'EXPIRED', 'CANCELLED'], true), 422, 'Status tidak valid.');
    }
}
