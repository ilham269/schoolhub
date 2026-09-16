<?php
// app/Contracts/PaymentGatewayInterface.php

namespace App\Contracts;

use App\Models\TagihanSpp;

interface PaymentGatewayInterface
{
    public function createTransaction(TagihanSpp $tagihan): array;

    public function verifySignature(array $payload): bool;
}