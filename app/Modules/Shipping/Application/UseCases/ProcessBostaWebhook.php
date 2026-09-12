<?php

namespace App\Modules\Shipping\Application\UseCases;

use App\Modules\Shipping\Domain\Contracts\ShipmentRepositoryInterface;
use App\Modules\Shipping\Domain\Contracts\ShipmentOperationRepositoryInterface;
use App\Modules\Shipping\Domain\Exceptions\ShippingException;

final class ProcessBostaWebhook
{
    public function __construct(
        private readonly ShipmentRepositoryInterface $shipments,
        private readonly ShipmentOperationRepositoryInterface $operations,
    )
    {
    }

    public function execute(array $payload): ?object
    {
        $reference = (string) ($payload['trackingNumber'] ?? $payload['_id'] ?? '');
        $businessReference = (string) ($payload['businessReference'] ?? '');
        $shipment = $reference !== '' ? $this->shipments->findByProviderReference($reference) : null;
        $shipment ??= $businessReference !== '' ? $this->shipments->findByIdempotencyKey($businessReference) : null;
        if ($shipment === null) {
            throw new ShippingException('Bosta webhook does not match a local shipment.');
        }

        $status = match ((int) ($payload['state'] ?? 0)) {
            45 => 'delivered',
            41 => 'out_for_delivery',
            30, 24 => 'in_transit',
            21, 23 => 'picked_up',
            48, 49, 100, 101 => 'cancelled',
            10, 20 => 'provider_created',
            default => 'provider_created',
        };
        $note = (string) ($payload['exceptionReason'] ?? 'Bosta state ' . ($payload['state'] ?? 'unknown'));
        $updated = $this->shipments->updateProviderStatus($shipment, $status, $note);
        $this->operations->complete((int) $updated->id, 'create', in_array($status, ['delivered', 'cancelled'], true) ? 'confirmed' : $status, $reference, $payload);
        return $updated;
    }
}
