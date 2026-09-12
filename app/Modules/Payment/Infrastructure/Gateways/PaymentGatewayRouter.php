<?php

namespace App\Modules\Payment\Infrastructure\Gateways;

use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Exceptions\PaymentException;

final class PaymentGatewayRouter implements PaymentGatewayInterface
{
    /** @var list<PaymentGatewayInterface> */
    private array $gateways;

    public function __construct(CashOnDeliveryGateway $cashOnDelivery, PaymobGateway $paymob)
    {
        $this->gateways = [$cashOnDelivery, $paymob];
    }

    public function supports(string $method): bool
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->supports($method)) {
                return true;
            }
        }
        return false;
    }

    public function createPayment(object $order, string $method, string $idempotencyKey): array
    {
        return $this->gatewayFor($method)->createPayment($order, $method, $idempotencyKey);
    }

    public function confirmPayment(object $payment): array
    {
        return $this->gatewayFor((string) $payment->method)->confirmPayment($payment);
    }

    public function refundPayment(object $payment): array
    {
        return $this->gatewayFor((string) $payment->method)->refundPayment($payment);
    }

    private function gatewayFor(string $method): PaymentGatewayInterface
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->supports($method)) {
                return $gateway;
            }
        }
        throw new PaymentException('Unsupported payment method.');
    }
}
