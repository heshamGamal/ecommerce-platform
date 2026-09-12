<?php

namespace App\Modules\Payment\Domain\Contracts;

use App\Models\CustomerOrder;

interface PaymentGatewayInterface
{
    public function createPayment(CustomerOrder $order, string $idempotencyKey): array;

    public function refund(CustomerOrder $order, int $amount): array;
}
