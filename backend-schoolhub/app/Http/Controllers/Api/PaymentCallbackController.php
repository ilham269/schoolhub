<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaymentCallbackJob;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function callback(Request $request, PaymentGatewayInterface $gateway)
    {
        $payload = $request->json()->all() ?: $request->all();
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') return response()->json(['message' => "Field {$field} wajib diisi."], 400);
        }
        ProcessPaymentCallbackJob::dispatch($payload);
        return response()->json(['status' => 'ok']);
    }
}
