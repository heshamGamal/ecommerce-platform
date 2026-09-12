<?php

namespace App\Modules\Payment\Infrastructure\Persistence;

use App\Models\Payment;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\PaymentNotFoundException;

final class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function find(int $paymentId): Payment
    {
        $payment = Payment::query()->with('order')->find($paymentId);
        if ($payment === null) throw new PaymentNotFoundException('Payment not found.');
        return $payment;
    }

    public function findForUserOrder(int $userId, int $orderId, int $paymentId): Payment
    {
        $payment = Payment::query()->with('order')->where('user_id', $userId)->where('order_id', $orderId)->find($paymentId);
        if ($payment === null) throw new PaymentNotFoundException('Payment not found.');
        return $payment;
    }

    public function findByIdempotencyKey(string $key): ?Payment
    {
        return Payment::query()->with('order')->where('idempotency_key', $key)->first();
    }

    public function listForOrder(int $userId, int $orderId): iterable
    {
        return Payment::query()->where('user_id', $userId)->where('order_id', $orderId)->latest()->get();
    }

    public function listForOrderAsAdmin(int $orderId): iterable
    {
        return Payment::query()->with('order')->where('order_id', $orderId)->latest()->get();
    }

    public function create(array $attributes): Payment
    {
        return Payment::query()->create($attributes)->load('order');
    }

    public function updateStatus(Payment $payment, string $status, array $attributes = []): Payment
    {
        $payment->update(array_merge($attributes, ['status' => $status]));
        return $payment->fresh(['order']);
    }
}
