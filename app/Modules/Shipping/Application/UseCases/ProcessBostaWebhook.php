<?php

namespace App\Modules\Shipping\Application\UseCases;

use App\Models\ShippingWebhookEvent;
use App\Modules\Shipping\Domain\Contracts\ShipmentRepositoryInterface;
use App\Modules\Shipping\Domain\Contracts\ShipmentOperationRepositoryInterface;
use App\Modules\Shipping\Domain\Exceptions\ShippingException;
use Illuminate\Database\QueryException;

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

        $eventId = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        try {
            ShippingWebhookEvent::query()->create([
                'provider' => 'bosta',
                'event_id' => $eventId,
                'event_type' => (string) ($payload['type'] ?? 'delivery_status'),
                'shipment_reference' => $reference !== '' ? $reference : $businessReference,
                'status' => 'received',
                'payload' => $payload,
            ]);
        } catch (QueryException $exception) {
            $existing = ShippingWebhookEvent::query()->where('provider', 'bosta')->where('event_id', $eventId)->first();
            if ($existing?->status === 'processed') {
                return $shipment;
            }
            if ($existing === null) {
                throw $exception;
            }
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
        $rank = ['pending' => 0, 'processing' => 0, 'provider_created' => 1, 'picked_up' => 2, 'in_transit' => 3, 'out_for_delivery' => 4, 'delivered' => 5, 'cancelled' => 5];
        if (($rank[$shipment->status] ?? 0) > ($rank[$status] ?? 0) || (in_array($shipment->status, ['delivered', 'cancelled'], true) && $shipment->status !== $status)) {
            ShippingWebhookEvent::query()->where('provider', 'bosta')->where('event_id', $eventId)->update(['status' => 'processed', 'processed_at' => now()]);
            return $shipment;
        }
        $note = (string) ($payload['exceptionReason'] ?? 'Bosta state ' . ($payload['state'] ?? 'unknown'));
        $updated = $this->shipments->updateProviderStatus($shipment, $status, $note);
        $this->operations->complete((int) $updated->id, 'create', in_array($status, ['delivered', 'cancelled'], true) ? 'confirmed' : $status, $reference, $payload);
        ShippingWebhookEvent::query()->where('provider', 'bosta')->where('event_id', $eventId)->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processing_error' => null,
        ]);
        return $updated;
    }
}
