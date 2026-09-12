<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\InvalidPaymentTransitionException;

final class RefundPayment
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    public function execute(int $paymentId): object
    {
        $payment = $this->payments->find($paymentId);
        if ($payment->status !== 'paid') throw InvalidPaymentTransitionException::from($payment->status, 'refunded');
        $result = $this->gateway->refundPayment($payment);
        return $this->payments->updateStatus($payment, $result['status'], ['metadata' => $result['metadata'] ?? $payment->metadata]);
    }
}
