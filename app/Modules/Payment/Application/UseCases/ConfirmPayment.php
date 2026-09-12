<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Contracts\TransactionManagerInterface;
use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\InvalidPaymentTransitionException;
use App\Modules\Payment\Domain\Exceptions\PaymentException;
use App\Modules\Payment\Domain\Exceptions\PaymentFailedException;

final class ConfirmPayment
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
        if ($payment->status !== 'pending') {
            throw InvalidPaymentTransitionException::from($payment->status, 'paid');
        }
        $order = $this->orders->find($payment->order_id);
        if (! in_array($order->status, ['pending', 'confirmed', 'processing'], true)) {
            throw new PaymentException('Payment cannot be confirmed for this order.');
        }
        $result = $this->gateway->confirmPayment($payment);
        if (($result['status'] ?? null) !== 'paid') {
            throw new PaymentFailedException('Payment confirmation failed.');
        }

        return $this->transactions->run(function () use ($paymentId, $order, $result): object {
            $locked = $this->payments->findForUpdate($paymentId);
            if ($locked->status !== 'pending') {
                throw InvalidPaymentTransitionException::from($locked->status, 'paid');
            }
            $confirmed = $this->payments->updateStatus($locked, 'paid', [
                'provider_reference' => $result['provider_reference'] ?? $locked->provider_reference,
                'metadata' => $result['metadata'] ?? $locked->metadata,
            ]);
            if ($order->status === 'pending') {
                $this->orders->updateStatus($order->id, 'confirmed');
            }

            return $confirmed;
        });
    }
}
