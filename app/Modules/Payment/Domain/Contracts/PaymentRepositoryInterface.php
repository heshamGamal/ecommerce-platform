<?php

namespace App\Modules\Payment\Domain\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function find(int $paymentId): Payment;

    public function findForUserOrder(int $userId, int $orderId, int $paymentId): Payment;

    public function findByIdempotencyKey(string $key): ?Payment;

    public function listForOrder(int $userId, int $orderId): iterable;

    public function listForOrderAsAdmin(int $orderId): iterable;

    public function create(array $attributes): Payment;

    public function updateStatus(Payment $payment, string $status, array $attributes = []): Payment;
}
