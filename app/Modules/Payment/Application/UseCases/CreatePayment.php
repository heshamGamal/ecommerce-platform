<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Exceptions\AuthenticationException;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\PaymentAmountMismatchException;
use App\Modules\Payment\Domain\Exceptions\PaymentException;
use App\Modules\Payment\Domain\Exceptions\PaymentFailedException;
use App\Modules\Payment\Domain\ValueObjects\PaymentData;

final class CreatePayment
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authentication,
        private readonly OrderRepositoryInterface $orders,
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    public function execute(int $orderId, PaymentData $data): object
    {
        $user = $this->authentication->user();
        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }
        if ($data->method !== 'cash_on_delivery') {
            throw new PaymentException('Unsupported payment method.');
        }

        $order = $this->orders->findForUser($user->id, $orderId);
        if ($data->currency !== $order->currency) {
            throw new PaymentAmountMismatchException('Payment currency does not match the order.');
        }
        if ($data->amount !== null && $data->amount !== $order->total_amount) {
            throw new PaymentAmountMismatchException('Payment amount does not match the order.');
        }

        $existing = $this->payments->findByIdempotencyKey($data->idempotencyKey);
        if ($existing !== null) {
            if ($existing->order_id !== $order->id) {
                throw new PaymentException('Idempotency key belongs to another order.');
            }

            return $existing;
        }

        $result = $this->gateway->createPayment($order, $data->idempotencyKey);
        if (($result['status'] ?? null) === 'failed') {
            throw new PaymentFailedException('Payment creation failed.');
        }

        return $this->payments->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'method' => $data->method,
            'provider_reference' => $result['provider_reference'],
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'status' => $result['status'],
            'idempotency_key' => $data->idempotencyKey,
            'metadata' => $result['metadata'] ?? null,
        ]);
    }
}
