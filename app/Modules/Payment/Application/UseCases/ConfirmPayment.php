<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Models\Payment;
use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\InvalidPaymentTransitionException;

final class ConfirmPayment
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    public function execute(int $paymentId): Payment
    {
        $payment = $this->payments->find($paymentId);
        if ($payment->status !== 'pending') throw InvalidPaymentTransitionException::from($payment->status, 'paid');
        $result = $this->gateway->confirmPayment($payment);
        return $this->payments->updateStatus($payment, $result['status'], ['metadata' => $result['metadata'] ?? $payment->metadata]);
    }
}
