<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Pembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(private array $payload) {}

    public function handle(PaymentGatewayInterface $gateway): void
    {
        try {
            if (!$gateway->verifySignature($this->payload)) {
                Log::warning('Callback Midtrans ditolak: signature tidak valid', ['order_id' => $this->payload['order_id'] ?? null]);
                return;
            }

            DB::transaction(function (): void {
                // Setelah callback pertama transaction_id diisi ID transaksi Midtrans.
                // order_id asal tetap tersedia di raw_callback untuk callback retry.
                $payment = Pembayaran::where(function ($query): void {
                    $query->where('transaction_id', $this->payload['order_id'])
                        ->orWhere('raw_callback->order_id', $this->payload['order_id']);
                })->lockForUpdate()->first();
                if (!$payment) {
                    Log::warning('Callback Midtrans tanpa pembayaran lokal', ['order_id' => $this->payload['order_id'] ?? null]);
                    return;
                }
                if ($payment->status === 'SUCCESS') {
                    Log::info('Callback Midtrans duplikat diabaikan', ['pembayaran_id' => $payment->id]);
                    return;
                }

                $status = $this->mapStatus((string) ($this->payload['transaction_status'] ?? ''));
                $va = $this->payload['va_numbers'][0] ?? [];
                $payment->update([
                    'transaction_id' => $this->payload['transaction_id'] ?? $this->payload['order_id'],
                    'payment_type' => $this->payload['payment_type'] ?? null,
                    'va_number' => $va['va_number'] ?? ($this->payload['permata_va_number'] ?? null),
                    'bank' => $va['bank'] ?? null,
                    'status' => $status,
                    'paid_at' => $status === 'SUCCESS' ? now() : null,
                    'expired_at' => $status === 'EXPIRED' ? now() : null,
                    'raw_callback' => $this->payload,
                    'signature_verified' => true,
                    'callback_received_at' => now(),
                    'processed_by_job_at' => now(),
                ]);

                $tagihan = $payment->tagihan()->lockForUpdate()->firstOrFail();
                if ($status === 'SUCCESS') {
                    $tagihan->update(['status' => 'LUNAS', 'paid_at' => now()]);
                    GenerateKuitansiPdf::dispatch($tagihan->id)->afterCommit();
                } elseif (in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED'], true) && $tagihan->status !== 'LUNAS') {
                    $tagihan->update(['status' => 'UNPAID']);
                }
            });
        } catch (\Throwable $exception) {
            Log::error('Gagal memproses callback Midtrans', ['order_id' => $this->payload['order_id'] ?? null, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'settlement', 'capture' => 'SUCCESS',
            'expire' => 'EXPIRED',
            'cancel' => 'CANCELLED',
            'deny', 'failure' => 'FAILED',
            default => 'PENDING',
        };
    }
}
