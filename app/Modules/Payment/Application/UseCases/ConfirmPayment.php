<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\InvalidPaymentTransitionException;
use App\Modules\Payment\Domain\Exceptions\PaymentException;

final class ConfirmPayment
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentGatewayInterface $gateway,
        private readonly OrderRepositoryInterface $orders,
    ) {}

    public function execute(int $paymentId): object
    {
        $payment = $this->payments->find($paymentId);
        if ($payment->status !== 'pending') throw InvalidPaymentTransitionException::from($payment->status, 'paid');
        $order = $this->orders->find($payment->order_id);
        if (!in_array($order->status, ['pending', 'confirmed', 'processing'], true)) {
            throw new PaymentException('Payment cannot be confirmed for this order.');
        }
        $result = $this->gateway->confirmPayment($payment);
        $confirmed = $this->payments->updateStatus($payment, $result['status'], ['metadata' => $result['metadata'] ?? $payment->metadata]);
        if ($order->status === 'pending') {
            $this->orders->updateStatus($order->id, 'confirmed');
        }
        return $confirmed;
    }
}
