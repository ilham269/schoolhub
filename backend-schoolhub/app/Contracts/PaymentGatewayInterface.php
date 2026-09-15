<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\TagihanSpp;

interface PaymentGatewayInterface
{
    public function createTransaction(TagihanSpp $tagihan): array;

    public function verifySignature(array $payload): bool;
}
