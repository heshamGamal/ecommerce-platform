<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Contracts\TransactionManagerInterface;
use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\InvalidPaymentTransitionException;
use App\Modules\Payment\Domain\Exceptions\PaymentFailedException;

final class RefundPayment
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentGatewayInterface $gateway,
        private readonly OrderRepositoryInterface $orders,
        private readonly TransactionManagerInterface $transactions,
    ) {}

    public function execute(int $paymentId): object
    {
        $payment = $this->payments->find($paymentId);
        if ($payment->status !== 'paid') {
            throw InvalidPaymentTransitionException::from($payment->status, 'refunded');
        }
        $result = $this->gateway->refundPayment($payment);
        if (($result['status'] ?? null) !== 'refunded') {
            throw new PaymentFailedException('Payment refund failed.');
        }

        return $this->transactions->run(function () use ($paymentId, $payment, $result): object {
            $locked = $this->payments->findForUpdate($paymentId);
            if ($locked->status !== 'paid') {
                throw InvalidPaymentTransitionException::from($locked->status, 'refunded');
            }
            $refunded = $this->payments->updateStatus($locked, 'refunded', [
                'metadata' => $result['metadata'] ?? $locked->metadata,
            ]);
            $this->orders->markRefunded($payment->order_id);

            return $refunded;
        });
    }
}
