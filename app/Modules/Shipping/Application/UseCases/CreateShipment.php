<?php

namespace App\Modules\Shipping\Application\UseCases;

use App\Models\Shipment;
use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Exceptions\AuthenticationException;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Shipping\Application\DTOs\CreateShipmentData;
use App\Modules\Shipping\Domain\Contracts\ShippingMethodRepositoryInterface;
use App\Modules\Shipping\Domain\Contracts\ShippingRateCalculatorInterface;
use App\Modules\Shipping\Domain\Contracts\ShipmentRepositoryInterface;
use App\Modules\Shipping\Domain\Exceptions\ShippingException;

final class CreateShipment
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authentication,
        private readonly OrderRepositoryInterface $orders,
        private readonly ShippingMethodRepositoryInterface $methods,
        private readonly ShippingRateCalculatorInterface $rates,
        private readonly ShipmentRepositoryInterface $shipments,
    ) {}

    public function execute(int $orderId, CreateShipmentData $data): Shipment
    {
        $user = $this->authentication->user();
        if ($user === null) throw new AuthenticationException('Unauthenticated.');
        $order = $this->orders->findForUser($user->id, $orderId);
        $method = $this->methods->find($data->shippingMethodId);
        if (!$method->is_active) throw new ShippingException('Shipping method is inactive.');
        if ($method->currency !== $order->currency) throw new ShippingException('Shipping currency does not match the order.');
        $existing = $this->shipments->findByIdempotencyKey($data->idempotencyKey);
        if ($existing !== null) {
            if ($existing->order_id !== $order->id) throw new ShippingException('Idempotency key belongs to another order.');
            return $existing;
        }
        $fee = $this->rates->calculate($order, $method);
        return $this->shipments->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'shipping_method_id' => $method->id,
            'method_code' => $method->code,
            'fee' => $fee,
            'currency' => $order->currency,
            'status' => 'pending',
            'address_snapshot' => $order->shipping_address,
            'idempotency_key' => $data->idempotencyKey,
            'metadata' => ['carrier' => $method->carrier],
        ]);
    }
}
