<?php
// app/Services/MidtransService.php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\TagihanSpp;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService implements PaymentGatewayInterface
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Buat Snap token untuk sebuah tagihan.
     * Order ID dibikin unik per attempt (bukan cuma invoice_number),
     * biar retry pembayaran gak bentrok sama transaksi lama di Midtrans.
     */
    public function createTransaction(TagihanSpp $tagihan): array
    {
        $orderId = $tagihan->invoice_number . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $tagihan->total,
            ],
            'customer_details' => [
                'first_name' => $tagihan->murid->Nama_lengkap_murid,
                'email' => $tagihan->murid->user->email,
                'phone' => $tagihan->murid->nomor_telepon,
            ],
            'item_details' => [[
                'id' => $tagihan->invoice_number,
                'price' => (int) $tagihan->total,
                'quantity' => 1,
                'name' => 'SPP - ' . $tagihan->periode->format('F Y'),
            ]],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            Log::info('Midtrans snap token created', [
                'tagihan_id' => $tagihan->id,
                'order_id' => $orderId,
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ];
        } catch (\Exception $e) {
            Log::error('Gagal membuat Midtrans snap token', [
                'tagihan_id' => $tagihan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifikasi signature key dari callback Midtrans.
     * Formula resmi Midtrans: sha512(order_id + status_code + gross_amount + server_key)
     */
    public function verifySignature(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $serverKey = config('services.midtrans.server_key');
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, $signatureKey);
    }
}