<?php

namespace App\Modules\Payment\Domain\Contracts;

interface PaymentGatewayInterface
{
    public function supports(string $method): bool;

    public function createPayment(object $order, string $idempotencyKey): array;

    public function confirmPayment(object $payment): array;

    public function refundPayment(object $payment): array;
}
