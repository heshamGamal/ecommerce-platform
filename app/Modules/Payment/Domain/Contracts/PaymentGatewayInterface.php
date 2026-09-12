<?php

namespace App\Modules\Payment\Domain\Contracts;

use App\Models\CustomerOrder;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createPayment(CustomerOrder $order, string $idempotencyKey): array;

    public function confirmPayment(Payment $payment): array;

    public function refundPayment(Payment $payment): array;
}
