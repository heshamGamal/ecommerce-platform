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
use App\Modules\Payment\Domain\Exceptions\PaymentInProgressException;
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
        if (! $this->gateway->supports($data->method)) {
            throw new PaymentException('Unsupported payment method.');
        }

        $order = $this->orders->findForUser($user->id, $orderId);
        if ($data->currency !== $order->currency) {
            throw new PaymentAmountMismatchException('Payment currency does not match the order.');
        }
        if ($data->amount !== null && $data->amount !== $order->total_amount) {
            throw new PaymentAmountMismatchException('Payment amount does not match the order.');
        }

        $claim = $this->payments->claim($data->idempotencyKey, [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'method' => $data->method,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'metadata' => ['idempotency_key' => $data->idempotencyKey],
        ]);
        if (! $claim->acquired) {
            if ($claim->payment->order_id !== $order->id || $claim->payment->user_id !== $user->id) {
                throw new PaymentException('Idempotency key belongs to another order.');
            }
            if (in_array($claim->payment->status, ['pending', 'paid', 'refunded', 'failed'], true)) {
                return $claim->payment;
            }
            throw new PaymentInProgressException('Payment is already being initiated. Retry with the same idempotency key.');
        }

        try {
            $result = $this->gateway->createPayment($order, $data->idempotencyKey);
            if (($result['status'] ?? null) === 'failed') {
                throw new PaymentFailedException('Payment creation failed.');
            }
        } catch (\Throwable $exception) {
            $this->payments->updateStatus($claim->payment, 'failed', [
                'metadata' => [
                    'failure' => $exception->getMessage(),
                    'reconciliation_required' => true,
                ],
            ]);
            throw $exception;
        }

        return $this->payments->updateStatus($claim->payment, $result['status'], [
            'provider_reference' => $result['provider_reference'] ?? null,
            'metadata' => $result['metadata'] ?? null,
        ]);
    }
}
